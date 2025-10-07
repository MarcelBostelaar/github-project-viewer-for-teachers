<?php
require_once __DIR__ . '/SubmissionFeedback.php';
require_once __DIR__ . '/CommitHistoryEntry.php';
enum SubmissionStatus : string{
    case MISSING = "missing";
    case NOTFOUND = "not_found";
    case VALID_URL = "valid_url";
}

class GithublinkSubmission{

    public string $url;
    private SubmissionStatus | null $status;
    public DateTime | null $submittedAt;
    private int $canvasID;

    private ?int $groupID;
    private Student $submittingStudent;

    public function __construct(string $url, int $canvasID, Student $submittingStudent, ?int $groupID, ?DateTime $submittedAt = null, SubmissionStatus $status = null){
        $this->url = $url;
        $this->canvasID = $canvasID;
        $this->groupID = $groupID;
        $this->status = $status;
        $this->submittedAt = $submittedAt;
        $this->submittingStudent = $submittingStudent;
    }

    public function getCanvasID(): int{
        return $this->canvasID;
    }

    public function getGroupID(): ?int{
        return $this->groupID;
    }


    /**
     * Summary of getStudents
     * @throws \Exception
     * @return Student[]
     */
    public function getStudents(): array{
        global $providers;
        if($this->groupID != null){
            return $providers->groupProvider->getStudentsInGroup($this->groupID);
        }
        return [$this->submittingStudent];
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
        return $providers->githubProvider->getCommitHistory($this->url);
    }

    /**
     * Summary of clone
     * @throws \Exception
     * @return string Succes or fail message
     */
    public function clone(): string{
        global $providers;
        return $providers->gitProvider->clone($this->url);
    }

    public function getStatus(): SubmissionStatus{
        if($this->status == null){
            if($this->url == ""){
                $this->status = SubmissionStatus::MISSING;
            } else {
                global $providers;
                $this->status = $providers->githubProvider->validateUrl($this->url) ? SubmissionStatus::VALID_URL : SubmissionStatus::NOTFOUND;
            }
        }
        return $this->status;
    }
}