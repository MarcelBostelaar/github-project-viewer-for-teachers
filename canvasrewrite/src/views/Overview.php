<?php

/**
 * Summary of renderFeedback
 * @param SubmissionFeedback[] $feedbacks
 * @return void
 */
function renderFeedback(array $feedbacks){
    usort($feedbacks, fn($a, $b) => $b->date <=> $a->date);
    foreach($feedbacks as $feedback){
        echo "<div style='border: 1px solid gray; padding: 5px; margin: 5px;'>";
        echo "At " . $feedback->date->format("Y-m-d H:i:s") . ", by $feedback->feedbackGiver:<br/>";
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

function statusToClass(SubmissionStatus $status): string{
    switch($status) {
        case SubmissionStatus::MISSING:
            return 'status-missing';
        case SubmissionStatus::NOTFOUND:
            return 'status-notfound';
        case SubmissionStatus::VALID_URL:
            return 'status-valid';
    }
    throw new Exception("Unknown status");
}

/**
 * Summary of RenderOverview
 * @param IGithublinkSubmission[] $Submissions
 * @return void
 */
function RenderOverview(array $Submissions){
    ?>

    <div class="submissions-container">
        <div class="filters">
            <div class="filter-group">
                <label for="student-filter">Filter by Student Name:</label>
                <input type="text" id="student-filter" placeholder="Enter student name..." onkeyup="filterTable()">
            </div>
            <div class="filter-group">
                <label for="section-filter">Filter by Section:</label>
                <input type="text" id="section-filter" placeholder="Enter section..." onkeyup="filterTable()">
            </div>
            <div class="filter-group">
                <label for="status-filter">Filter by Status:</label>
                <select id="status-filter" onchange="filterTable()">
                    <option value="">All Statuses</option>
                    <option value="<?= SubmissionStatus::VALID_URL->value ?>">Valid URL</option>
                    <option value="<?= SubmissionStatus::MISSING->value ?>">Not submitted</option>
                    <option value="<?= SubmissionStatus::NOTFOUND->value ?>">Not found (private?)</option>
                </select>
            </div>
        </div>

        <table class="submissions-table" id="submissions-table">
            <thead>
                <tr>
                    <th>Students</th>
                    <th>Sections</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                    <th>Feedback</th>
                    <th>Commit History</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($Submissions as $submission){
                    $students = $submission->getStudents();
                    $studentNames = array_map(fn($s) => htmlspecialchars($s->name), $students);
                    
                    // Get all sections for all students
                    $allSections = [];
                    foreach($students as $student) {
                        $studentSections = $student->getSections();
                        $allSections = array_merge($allSections, $studentSections);
                    }
                    $allSections = array_unique($allSections);
                    $sectionsText = implode(", ", array_map('htmlspecialchars', $allSections));
                    
                    $isGroup = $submission->getGroup() !== null;
                    $id = $isGroup ? $submission->getGroup()->id : $students[0]->id;
                    $idSection = $isGroup ? ("groupid=" . $submission->getGroup()->id) : ("userid=" . $students[0]->id);
                    ?>
                    <tr data-students="<?= strtolower(implode(' ', $studentNames)) ?>" 
                        data-sections="<?= strtolower($sectionsText) ?>" 
                        data-status="<?= $submission->getStatus()->value ?>">
                        <td><?= implode(",<br>", $studentNames) ?></td>
                        <td><?= $sectionsText ?></td>
                        <td><span class="<?= statusToClass($submission->getStatus()) ?>"><?= $submission->getStatus()->value ?></span></td>
                        <td><?= $submission->getSubmissionDate() ? $submission->getSubmissionDate()->format("Y-m-d H:i:s") : "Not submitted" ?></td>
                        <td>
                            <?php if($submission->getStatus() == SubmissionStatus::VALID_URL): ?>
                                <?php if($isGroup): ?>
                                    <?php $groupID = $submission->getGroup()->id; ?>
                                    <button class="clone-btn" onclick="cloneGroup(<?= $groupID ?>)">Clone</button>
                                <?php else: ?>
                                    <?php $userID = $students[0]->id; ?>
                                    <button class="clone-btn" onclick="cloneIndividual(<?= $userID ?>)">Clone</button>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="feedback-section">
                                <form method="post">
                                    <input type="hidden" name="action" value="addfeedback"/>
                                    <input type="hidden" name="<?= $isGroup ? "groupid" : 'userid' ?>" value="<?=$id?>"/>
                                    <textarea name="feedback" rows="4" cols="50" placeholder="Enter feedback here..." required></textarea><br/>
                                    <button type="submit">Add Feedback</button>
                                </form>
                                <?php if($submission->getStatus() == SubmissionStatus::VALID_URL): ?>
                                    <div postload="<?="?action=feedback&$idSection"?>">Loading feedback...</div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if($submission->getStatus() == SubmissionStatus::VALID_URL): ?>
                                <div class="commits-section">
                                    <div postload="<?="?action=commithistory&$idSection"?>">Loading commit history...</div>
                                </div>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src='/static/util/postloading.js'></script>
    <script src="/static/js/overview.js"></script>
    <link rel="stylesheet" href="/static/css/overview.css">
    <?php
}