<?php
require_once __DIR__ . '/../SubmissionFeedback.php';
require_once __DIR__ . '/../CommitHistoryEntry.php';
require_once __DIR__ . '/IGithublinkSubmission.php';

class IllegalCallToInvalidSubmissionException extends Exception{}

/**
 * Represents an actual submission in canvas by a single student. If the submission was made by a student then in a group, the groupID of the group that made the submission is stored.
 */
class ConcreteGithublinkSubmission implements IGithublinkSubmission{

    private string $url;
    private DateTime | null $submittedAt;
    private Student $submittingStudent;
    private int $canvasID;
    private ?Group $group;

    public function __construct(string $url, int $canvasID, Student $submittingStudent, ?Group $group, ?DateTime $submittedAt = null){
        $this->url = $url;
        $this->canvasID = $canvasID;
        $this->submittedAt = $submittedAt;
        $this->submittingStudent = $submittingStudent;
        $this->group = $group;
    }

    public function getCanvasID(): int{ //remove
        return $this->canvasID;
    }

    public function getGroup(): ?Group{
        return $this->group;
    }

    public function getUrl(): string{
        return $this->url;
    }

    /**
     * Returns the student who made the submission
     * @return Student[]
     */
    public function getStudents(): array{
        return [$this->submittingStudent];
    }

    public function getStudent(): Student{
        return $this->submittingStudent;
    }

    /**
     * Summary of getFeedback
     * @throws \Exception
     * @return SubmissionFeedback[]
     */
    public function getFeedback(): array{
        global $providers;
        return $providers->submissionProvider->getFeedbackForSubmission($this->canvasID);
    }

    /**
     * Summary of addFeedback
     * @param string $feedback
     * @throws \Exception
     * @return void
     */
    public function submitFeedback(string $feedback): void{
        global $providers;
        $providers->submissionProvider->submitFeedback($feedback, $this->canvasID);
    }

    /**
     * Summary of getCommitHistory
     * @throws \Exception
     * @return CommitHistoryEntry[]
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
        return $providers->githubProvider->validateUrl($this->url) ? SubmissionStatus::VALID_URL : SubmissionStatus::NOTFOUND;
    }

    public function getSubmissionDate(): ?DateTime{
        return $this->submittedAt;
    }
}
