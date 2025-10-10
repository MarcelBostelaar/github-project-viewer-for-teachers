<?php

namespace GithubProjectViewer\Models\GithublinkSubmission;

use GithubProjectViewer\Models as Models;

class CantDetermineValidURLException extends \Exception{}

/**
 * Allows multiple concrete submissions to be treated as one. Use to retroactively combine submissions from multiple students in a group assignment, for example, based on the groups as they exist now.
 * Prevents problems when students change groups after submitting.
 */
class CombinedGithublinkSubmission implements IGithublinkSubmission{

    /**
     * 
     * @var ConcreteGithublinkSubmission[]
     */
    private array $children;
    private Models\Group $group;

    public function __construct(Models\Group $group, ... $children){
        $this->children = $children;
        $this->group = $group;

        global $providers;
        $providers->virtualIDsProvider->getVirtualIdFor($this);
    }

    public function getStudents(): array{
        return array_merge(...array_map(fn($child) => $child->getStudents(), $this->children));
    }

    public function getFeedback(): array{
        $merged = array_merge(...array_map(fn($child) => $child->getFeedback(), $this->children));
        return array_unique_predicate(fn($x) => $x->feedbackGiver . $x->date->format('Y-m-d H:i:s') . $x->comment
        ,$merged);
    }

    public function submitFeedback(string $feedback): void{
        $prependingText = "Group " . $this->group->name . ":";
        $feedbackText = $prependingText . "\n" . $feedback;
        //Always prepend group name to feedback to avoid confusion, as an ex-member will not know which group the feedback is for otherwise.
        $encounteredGroups = [];
        foreach($this->children as $child){
            self::submitSingleFeedbackToChild($child, $encounteredGroups, $feedbackText);
        }
    }

    private static function submitSingleFeedbackToChild(ConcreteGithublinkSubmission $child, array &$encounteredGroups, string $feedback): void{
        $childSubmissionGroup = $child->getGroup();
        if($childSubmissionGroup === null){ //Submission without group, always submit feedback with name of group. No risk of duplicate submission.
            $child->submitFeedback($feedback);
            return;
        }
        if(array_key_exists($childSubmissionGroup->name, $encounteredGroups)){
            return; //Already submitted feedback for this group, skip
        }
        $encounteredGroups[$childSubmissionGroup->name] = true;
        $child->submitFeedback($feedback);
    }

    /**
     * Returns the child with the most recent commit.
     * @throws CantDetermineValidURLException
     * @throws IllegalCallToInvalidSubmissionException
     * @return ConcreteGithublinkSubmission
     */
    private function getMostLikelyValidChildOrThrow(): ConcreteGithublinkSubmission{
        $children = $this->children;
        $children = array_filter($children, fn($x) => $x->getStatus() == SubmissionStatus::VALID_URL);
        if(count($children) === 0){
            throw new IllegalCallToInvalidSubmissionException("No valid URLs found in combined submission");
        }
        $children = array_map(
            fn($x) => [
                "mostrecentcommit" => max(array_map(fn($y) => $y->date, $x->getCommitHistory())),
                "child" => $x],
            $children);
        uasort($children, fn($a, $b) => $b["mostrecentcommit"] <=> $a["mostrecentcommit"]);
        return array_values($children)[0]["child"];
    }

    /**
     * @throws CantDetermineValidURLException
     * @throws IllegalCallToInvalidSubmissionException
     * @return Models\CommitHistoryEntry[]
     */
    public function getCommitHistory(): array{
        return $this->getMostLikelyValidChildOrThrow()->getCommitHistory();
    }

    /**
     * @throws CantDetermineValidURLException
     * @throws IllegalCallToInvalidSubmissionException
     * @return string
     */

    public function clone(): string{
        return $this->getMostLikelyValidChildOrThrow()->clone();
    }

    public function getStatus(): SubmissionStatus{
        $validationHierarchy = [
            SubmissionStatus::MISSING,
            SubmissionStatus::NOTFOUND,
            SubmissionStatus::VALID_BUT_EMPTY,
            SubmissionStatus::VALID_URL,
        ];
        $highestFind = SubmissionStatus::MISSING;
        foreach($this->children as $child){
            if(array_search($child->getStatus(), $validationHierarchy) > array_search($highestFind, $validationHierarchy)){
                $highestFind = $child->getStatus();
            }
        }
        return $highestFind;
    }

    public function getSubmissionDate(): ?\DateTime{
        $dates = array_map(fn($child) => $child->getSubmissionDate(), $this->children);
        $dates = array_filter($dates, fn($d) => $d !== null);
        if(count($dates) === 0){
            return null;
        }
        return max($dates);
    }

    public function getGroup(): ?Models\Group{
        return $this->group;
    }

    public function getId(): string{
        global $providers;
        return $providers->virtualIDsProvider->getVirtualIdFor($this);
    }

    public function getUrl(): ?string{
        return $this->getMostLikelyValidChildOrThrow()->getUrl();
    }
}