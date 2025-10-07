<?php
require_once __DIR__ . '/IGithublinkSubmission.php';
require_once __DIR__ . '/../Group.php';

class CantDetermineValidURLException extends Exception{}

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
    private Group $group;

    public function __construct(Group $group, ... $children){
        $this->children = $children;
        $this->group = $group;
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

    private static function submitSingleFeedbackToChild(ConcreteGithublinkSubmission $child, array $encounteredGroups, string $feedback): void{
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
     * Returns the child submission with the best valid URL, or throws if none are valid.
     * If multiple valid URLs are found, returns the most common one.
     * If multiple valid URLs are tied for most common, throws an Exception.
     * @throws CantDetermineValidURLException
     * @throws IllegalCallToInvalidSubmissionException
     * @return ConcreteGithublinkSubmission
     */
    private function getMostLikelyValidChildOrThrow(): ConcreteGithublinkSubmission{
        $urls = [];
        foreach($this->children as $child){
            if($child->getStatus() === SubmissionStatus::VALID_URL){
                if(!array_key_exists($child->getUrl(), $urls)){
                    $urls[$child->getUrl()] = [];
                }
                $urls[$child->getUrl()][] = $child;
            }
        }
        if(count($urls) === 0){
            throw new IllegalCallToInvalidSubmissionException("No valid URLs found in combined submission");
        }
        $processed = array_map(fn($x) => [
            "count" => count($x),
            "a_child" => $x[0]
        ], $urls);
        usort($processed, fn($a, $b) => $b["count"] <=> $a["count"]);
        $highest = array_shift($processed);
        foreach($processed as $entry){
            if($entry["count"] < $highest["count"]){
                return $highest['a_child'];
            } else if($entry["count"] === $highest["count"]){
                throw new CantDetermineValidURLException("Multiple valid URLs tied for most common in combined submission");
            }
        }
        return $highest['a_child'];
    }

    /**
     * @throws CantDetermineValidURLException
     * @throws IllegalCallToInvalidSubmissionException
     * @return CommitHistoryEntry[]
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
        $didFindInvalid = false;
        foreach($this->children as $child){
            if($child->getStatus() === SubmissionStatus::VALID_URL){
                return SubmissionStatus::VALID_URL;
            }
            if($child->getStatus() === SubmissionStatus::NOTFOUND){
                $didFindInvalid = true;
            }
        }
        return $didFindInvalid ? SubmissionStatus::NOTFOUND : SubmissionStatus::MISSING;
    }

    public function getSubmissionDate(): ?DateTime{
        try{
            return $this->getMostLikelyValidChildOrThrow()->getSubmissionDate();
        }
        catch(IllegalCallToInvalidSubmissionException $e){
            return null;
        }
    }

    public function getGroup(): ?Group{
        return $this->group;
    }
}