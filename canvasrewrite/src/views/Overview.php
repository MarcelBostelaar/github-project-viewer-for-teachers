<?php

/**
 * @param SubmissionFeedback[] $Submissions
 */
function renderFeedback(array $feedbacks){
    usort($feedbacks, fn($a, $b) => $b->date <=> $a->date);
    foreach($feedbacks as $feedback){
        echo "<div style='border: 1px solid gray; padding: 5px; margin: 5px;'>";
        echo "At " . $feedback->date->format("Y-m-d H:i:s") . ":<br/>";
        echo nl2br(htmlspecialchars($feedback->comment));
        echo "</div>";
    }
}

/**
 * Summary of renderCommitHistory
 * @param CommitHistoryEntry[] $commits
 * @return void
 */
function renderCommitHistory(array $commits){
    usort($commits, fn($a, $b) => $b->date <=> $a->date);
    foreach($commits as $commit){
        echo "<div style='border: 1px solid gray; padding: 5px; margin: 5px;'>";
        echo "At " . $commit->date->format("Y-m-d H:i:s") . " by " . htmlspecialchars($commit->author) . ":<br/>";
        echo "<strong>" . htmlspecialchars($commit->name) . "</strong><br/>";
        echo nl2br(htmlspecialchars($commit->description));
        echo "</div>";
    }
}

/**
 * Summary of RenderOverview
 * @param IGithublinkSubmission[] $Submissions
 * @return void
 */
function RenderOverview(array $Submissions, string $selfURL){
    echo "<script src='/static/util/postloading.js'></script>";
    foreach($Submissions as $submission){
        echo "<div>";
        // echo "URL: " . htmlspecialchars($submission->get()) . "<br/>";
        echo "Status: " . $submission->getStatus()->value . "<br/>";
        echo "Submitted At: " . ($submission->getSubmissionDate() ? $submission->getSubmissionDate()->format("Y-m-d H:i:s") : "not submitted") . "<br/>";
        echo "Students: ";
        $students = $submission->getStudents();
        $studentNames = array_map(fn($s) => htmlspecialchars($s->name), $students);
        echo implode(", ", $studentNames);
        echo "<br/>";
        
        if($submission->getStatus() == SubmissionStatus::VALID_URL){
            $isGroup = $submission->getGroup() !== null;
            $idSection = $isGroup ? ("groupid=" . $submission->getGroup()->id) : ("userid=" . $students[0]->id);

            if($isGroup) {
                $groupID = $submission->getGroup()->id;
                echo "<button onclick='cloneGroup($groupID)'>Clone</button><br>";
            }
            else{
                $userID = $students[0]->id;
                echo "<button onclick='cloneIndividual($userID)'>Clone</button><br>";
            }

            ?>
            Feedback: <br>
            <div postload='<?="?action=feedback&$idSection"?>'>Loading feedback</div>
            
            Commit History: <br/>
            <div postload='<?="?action=commithistory&$idSection"?>'>Loading commit history</div>
            <?php
        }
        echo "</div>";
        echo "<hr/>";
    }
}