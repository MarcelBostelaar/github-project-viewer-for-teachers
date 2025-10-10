<?php

namespace GithubProjectViewer\Models\GithublinkSubmission;
use GithubProjectViewer\Models as Models;

class IllegalCallToInvalidSubmissionException extends \Exception{}

/**
 * Represents an actual submission in canvas by a single student. If the submission was made by a student then in a group, the groupID of the group that made the submission is stored.
 */
class ConcreteGithublinkSubmission implements IGithublinkSubmission{

    private string $url;
    private \DateTime | null $submittedAt;
    private Models\Student $submittingStudent;
    private int $canvasID;

    public function __construct(string $url, int $canvasID, Models\Student $submittingStudent, ?\DateTime $submittedAt = null){
        $this->url = $url;
        $this->canvasID = $canvasID;
        $this->submittedAt = $submittedAt;
        $this->submittingStudent = $submittingStudent;
        
        global $providers;
        $providers->virtualIDsProvider->getVirtualIdFor($this);
    }

    public function getCanvasID(): int{ //remove
        return $this->canvasID;
    }

    public function getGroup(): ?Models\Group{
        global $providers;
        $lookup = $providers->groupProvider->getStudentGroupLookup();
        $groups = $lookup->getItem($this->submittingStudent);
        if(count($groups) == 0){
            return null;
        }
        if(count($groups) > 1){
            throw new \Exception("Student in multiple groups, should not be possible");
        }
        return $groups[0];
    }

    public function getUrl(): string{
        return $this->url;
    }

    /**
     * Returns the student who made the submission
     * @return Models\Student[]
     */
    public function getStudents(): array{
        return [$this->submittingStudent];
    }

    public function getStudent(): Models\Student{
        return $this->submittingStudent;
    }

    /**
     * Summary of getFeedback
     * @throws \Exception
     * @return Models\SubmissionFeedback[]
     */
    public function getFeedback(): array{
        global $providers;
        return $providers->submissionProvider->getFeedbackForSubmission($this->submittingStudent->id);
    }

    /**
     * Summary of addFeedback
     * @param string $feedback
     * @throws \Exception
     * @return void
     */
    public function submitFeedback(string $feedback): void{
        global $providers;
        $providers->submissionProvider->submitFeedback($feedback, $this);
    }

    /**
     * Summary of getCommitHistory
     * @throws \Exception
     * @return Models\CommitHistoryEntry[]
     */
    public function getCommitHistory(): array{
        global $providers;
        if($this->getStatus() !== SubmissionStatus::VALID_URL){
            throw new IllegalCallToInvalidSubmissionException("Cannot get commit history for invalid URL");
        }
        return $providers->githubProvider->getCommitHistory($this->url);
    }

    /**
     * Summary of clone
     * @throws \Exception
     * @return string Succes or fail message
     */
    public function clone(): string{
        if($this->getStatus() !== SubmissionStatus::VALID_URL){
            throw new IllegalCallToInvalidSubmissionException("Cannot get commit history for invalid URL");
        }
        global $providers;
        return $providers->gitProvider->clone($this->url);
    }

    public function getStatus(): SubmissionStatus{
        if($this->url == ""){
            return SubmissionStatus::MISSING;
        } 
        global $providers;
        return $providers->githubProvider->validateUrl($this->url);
    }

    public function getSubmissionDate(): ?\DateTime{
        return $this->submittedAt;
    }

    public function getId(): string{
        global $providers;
        return $providers->virtualIDsProvider->getVirtualIdFor($this);
    }
}
