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
 * Summary of RenderOverview
 * @param IGithublinkSubmission[] $Submissions
 * @return void
 */
function RenderOverview(array $Submissions){
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
            // $id = $submission->getCanvasID();
            echo "<button onclick='clone(-1)'>Clone</button><br>";
        }
        
        echo "Feedback: ";
        $feedbacks = $submission->getFeedback();
        renderFeedback($feedbacks);
        
        echo "Commit History: <br/>";
        if($submission->getStatus() != SubmissionStatus::VALID_URL){
            echo "<em>No commit history available due to invalid submission status.</em>";
        }
        else{
            $commits = $submission->getCommitHistory();
            usort($commits, fn($a, $b) => $b->date <=> $a->date);
            foreach($commits as $commit){
                echo "<div style='border: 1px solid gray; padding: 5px; margin: 5px;'>";
                echo "At " . $commit->date->format("Y-m-d H:i:s") . " by " . htmlspecialchars($commit->author) . ":<br/>";
                echo "<strong>" . htmlspecialchars($commit->name) . "</strong><br/>";
                echo nl2br(htmlspecialchars($commit->description));
                echo "</div>";
            }
        }
        echo "</div>";
        echo "<hr/>";
    }
}