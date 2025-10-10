<?php

/**
 * Summary of renderFeedback
 * @param SubmissionFeedback[] $feedbacks
 * @return void
 */
function renderFeedback(array $feedbacks){
    usort($feedbacks, fn($a, $b) => $b->date <=> $a->date);
    echo "<div class=feedback-container>";
    foreach($feedbacks as $feedback){
        echo "<div class='feedback_entry'>";
        echo "<h5 class=author>$feedback->feedbackGiver - " . $feedback->date->format("Y-m-d H:i:s") . ":</h5>";
        echo nl2br(htmlspecialchars($feedback->comment));
        echo "</div>";
    }
    echo "</div>";
}

function timeAgo(DateTime $past): string {
    $now = new DateTime();
    $diff = $now->getTimestamp() - $past->getTimestamp();

    $units = [
        'year' => 365 * 24 * 60 * 60,
        'month' => 30 * 24 * 60 * 60,
        'week' => 7 * 24 * 60 * 60,
        'day' => 24 * 60 * 60,
        'hour' => 60 * 60,
        'minute' => 60,
        'second' => 1,
    ];

    foreach ($units as $name => $seconds) {
        if ($diff >= $seconds) {
            $value = floor($diff / $seconds);
            return "$value $name" . ($value > 1 ? 's' : '') . " ago";
        }
    }
    return "just now";
}

/**
 * Summary of renderCommitHistory
 * @param CommitHistoryEntry[] $commits
 * @return void
 */
function renderCommitHistory(array $commits, $limit, $id){
    usort($commits, fn($a, $b) => $b->date <=> $a->date);
    $cutOff = max(0, count($commits) - $limit);
    $commits = array_slice($commits, 0, $limit);
    echo '<div class="commits-section">';
    foreach($commits as $commit){
        $url = $commit->url ? "href='" . htmlspecialchars($commit->url) . "'" : "";
        echo "<a $url><div class='commit_message'>";
        echo "<h5 class=author>" . timeAgo($commit->date) . " by " . htmlspecialchars($commit->author) . ":</h5>";
        echo nl2br(htmlspecialchars($commit->description));
        echo "</div></a>";
    }
    if($cutOff > 0){
        $newCount = $limit + 10;
        echo "<div class='commit_message clickable' onclick='LoadMoreCommits(this, $newCount, `$id`)'>... and $cutOff more.</div>";
    }
    echo "</div>";
}

function statusToClass(SubmissionStatus $status): string{
    switch($status) {
        case SubmissionStatus::MISSING:
            return 'status-missing';
        case SubmissionStatus::NOTFOUND:
            return 'status-notfound';
        case SubmissionStatus::VALID_URL:
            return 'status-valid';
        case SubmissionStatus::VALID_BUT_EMPTY:
            return 'status-empty';
    }
    throw new Exception("Unknown status");
}

function RenderSubmissionRowStub(IGithublinkSubmission $submission, string $baseURL){
    $id = $submission->getId();
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
    ?>
    <tr data-students="<?= strtolower(implode(' ', $studentNames)) ?>" 
        data-sections="<?= strtolower($sectionsText) ?>" 
        data-status="loading"
        postload="<?=$baseURL?>&action=submissionrow&id=<?=$submission->getId();?>">
        <td><?= implode(",<br>", $studentNames) ?></td>
        <td><?= $sectionsText ?></td>
        <td>Loading status</td>
        <td><?= $submission->getSubmissionDate() ? $submission->getSubmissionDate()->format("Y-m-d H:i:s") : "Not submitted" ?></td>
        <td>
            Loading...
        </td>
        <td>
            Loading...
        </td>
        <td>
            Loading...
        </td>
    </tr>
    <?php
}

function RenderSubmissionRow(IGithublinkSubmission $submission, string $baseURL){
    $id = $submission->getId();
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
    $status = $submission->getStatus();
    $appendToStatus = "";
    if($status == SubmissionStatus::NOTFOUND){
        $url = $submission->getUrl();
        formatted_var_dump($submission);
        $appendToStatus = "<br>Submitted URL:<br><a href='$url'>$url</a>";
    }
    ?>
    <tr data-students="<?= strtolower(implode(' ', $studentNames)) ?>" 
        data-sections="<?= strtolower($sectionsText) ?>" 
        data-status="<?= $submission->getStatus()->value ?>"
        data-id="<?= $id ?>">
        <td><?= implode(",<br>", $studentNames) ?></td>
        <td><?= $sectionsText ?></td>
        <td><span class="<?= statusToClass($submission->getStatus()) ?>"><?=$status->value . $appendToStatus?></span></td>
        <td><?= $submission->getSubmissionDate() ? $submission->getSubmissionDate()->format("Y-m-d H:i:s") : "Not submitted" ?></td>
        <td>
            <?php if($submission->getStatus() == SubmissionStatus::VALID_URL): ?>
                <button class="clone-btn" onclick="clone(this, '<?= $id ?>')">Clone</button>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
        <td>
            <div class="feedback-section">
                <div>
                    <textarea name="feedback" rows="4" cols="50" placeholder="Enter feedback here..." required></textarea><br/>
                    <button onclick="return submitFeedback(this, '<?=$id?>')">Add Feedback</button>
                </div>
                <div class="feedback-container" postload="<?="$baseURL&action=feedback&id=$id"?>">
                    <div>Loading feedback...</div>
                </div>
            </div>
        </td>
        <td>
            <?php if($submission->getStatus() == SubmissionStatus::VALID_URL): ?>
                <div class="commits-section" postload="<?="$baseURL&action=commithistory&id=$id"?>">
                    <div>Loading commit history...</div>
                </div>
            <?php else: ?>
                -
            <?php endif;?>
        </td>
    </tr>
    <?php
}

/**
 * Summary of RenderOverview
 * @param IGithublinkSubmission[] $Submissions
 * @return void
 */
function RenderOverview(array $Submissions, $baseURL){
    ?>
    <button onclick="clearCache()">Fully clear cache (slow)</button>
    <button onclick="clearCache('github')">Clear github cache</button>
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
                    <option value="<?= SubmissionStatus::VALID_BUT_EMPTY->value ?>">Empty repository</option>
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
                    RenderSubmissionRowStub($submission, $baseURL);
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