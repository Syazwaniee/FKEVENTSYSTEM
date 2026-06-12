<?php

include 'config.php';

function insertUser(
    $student_id,
    $fullname,
    $ic_number,
    $phone,
    $email,
    $role,
    $club_id,
    $committee_role_id
) {
    global $conn;

    // Default password = hashed student id
    $password_hash = password_hash(
        $student_id,
        PASSWORD_DEFAULT
    );

    // Default active status
    $is_active = 1;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert into user table
        $stmt_user = $conn->prepare(
            "INSERT INTO user
            (
                Student_id,
                Fullname,
                icNum,
                Phone_Num,
                Email,
                role,
                Password_hash,
                is_active
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt_user) {
            throw new Exception("Prepare Failed (user): " . $conn->error);
        }

        $stmt_user->bind_param(
            "sssssssi",
            $student_id,
            $fullname,
            $ic_number,
            $phone,
            $email,
            $role,
            $password_hash,
            $is_active
        );

        if (!$stmt_user->execute()) {
            throw new Exception("Execute Failed (user): " . $stmt_user->error);
        }

        $user_id = $conn->insert_id;

        // ================= INSERT COMMITTEE AND CLUB MEMBER =================
        if (strtolower($role) === "committee" && !empty($club_id) && !empty($committee_role_id)) {
            // Insert into clubcommitee table
            $stmt_committee = $conn->prepare(
                "INSERT INTO clubcommitee
                (
                    User_id,
                    Club_id,
                    Committee_role_id
                )
                VALUES (?, ?, ?)"
            );

            if (!$stmt_committee) {
                throw new Exception("Prepare Failed (committee): " . $conn->error);
            }

            $stmt_committee->bind_param(
                "iii",
                $user_id,
                $club_id,
                $committee_role_id
            );

            if (!$stmt_committee->execute()) {
                throw new Exception("Execute Failed (committee): " . $stmt_committee->error);
            }

            $stmt_committee->close();

            // Insert into clubmember table
            $clubStatus = "Active";
            $joined_date = date("Y-m-d"); // Use current date

            $stmt_clubMember = $conn->prepare(
                "INSERT INTO clubmember
                (
                    User_id,
                    Club_id,
                    Joined_date,
                    clubStatus,
                    maxCapacity
                )
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt_clubMember) {
                throw new Exception("Prepare Failed (clubmember): " . $conn->error);
            }

            $stmt_clubMember->bind_param(
                "iisisi",
                $user_id,
                $club_id,
                $joined_date,
                $clubStatus,
                $maxCapacity
            );

            if (!$stmt_clubMember->execute()) {
                throw new Exception("Execute Failed (clubmember): " . $stmt_clubMember->error);
            }

            $stmt_clubMember->close();
        }

        // ================= COMMIT =================
        $conn->commit();

        $stmt_user->close();

        return true;
    } catch (Exception $e) {
        $conn->rollback();

        die("TRANSACTION FAILED: " . $e->getMessage());
    }
}

function getClubs()
{
    global $conn;

    $sql = "SELECT * FROM club";
    $result = $conn->query($sql);

    return $result;
}

/**
 * Add a new club.
 *
 * @return array{success:bool,message:string}
 */
function clubMaxCapacityFromRow(array $row): ?int
{
    foreach (['maxCapacity', 'Max_capacity', 'maximum_capacity', 'Maximum_capacity'] as $key) {
        if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
            return (int) $row[$key];
        }
    }

    return null;
}

function insertClub(
    string $clubName,
    string $description,
    string $advisorName,
    int $isActive,
    $maxCapacity
): array {
    global $conn;

    $clubName = trim($clubName);
    $description = trim($description);
    $advisorName = trim($advisorName);
    $status = $isActive === 1 ? 'Active' : 'Inactive';
    $maxCapacity = (int) trim((string) $maxCapacity);

    if ($clubName === '') {
        return ['success' => false, 'message' => 'Club name is required.'];
    }

    if ($advisorName === '') {
        return ['success' => false, 'message' => 'Advisor name is required.'];
    }

    if ($maxCapacity <= 0) {
        return ['success' => false, 'message' => 'Maximum capacity must be a positive number.'];
    }

    $createdAt = date('Y-m-d H:i:s');

    $stmt = $conn->prepare(
        'INSERT INTO club (Club_name, Description, advisorName, clubStatus, maxCapacity, Created_at)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param('ssssis', $clubName, $description, $advisorName, $status, $maxCapacity, $createdAt);

    if ($stmt->execute()) {
        $stmt->close();

        return ['success' => true, 'message' => 'Club added successfully.'];
    }

    $message = $stmt->error;
    $stmt->close();

    return ['success' => false, 'message' => 'Could not add club: ' . $message];
}

/**
 * Load one club for the edit form.
 *
 * @return array<string, mixed>|null
 */
function getClubById(int $clubId): ?array
{
    global $conn;

    if ($clubId <= 0) {
        return null;
    }

    $stmt = $conn->prepare('SELECT * FROM club WHERE Club_id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $clubId);
    if (!$stmt->execute()) {
        $stmt->close();

        return null;
    }

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return null;
    }

    $maxCap = clubMaxCapacityFromRow($row);

    return [
        'Club_id' => (int) ($row['Club_id'] ?? $row['club_id'] ?? 0),
        'club_name' => (string) ($row['Club_name'] ?? ''),
        'description' => clubDescriptionFromRow($row),
        'advisor_name' => clubAdvisorTextFromRow($row),
        'club_status' => clubIsActiveFromRow($row) ? '1' : '0',
        'max_capacity' => $maxCap !== null ? (string) $maxCap : '',
    ];
}

/**
 * Update an existing club.
 *
 * @return array{success:bool,message:string}
 */
function updateClub(
    int $clubId,
    string $clubName,
    string $description,
    string $advisorName,
    int $isActive,
    $maxCapacity
): array {
    global $conn;

    if ($clubId <= 0) {
        return ['success' => false, 'message' => 'Invalid club ID.'];
    }

    $clubName = trim($clubName);
    $description = trim($description);
    $advisorName = trim($advisorName);
    $status = $isActive === 1 ? 'Active' : 'Inactive';
    $maxCapacity = (int) trim((string) $maxCapacity);

    if ($clubName === '') {
        return ['success' => false, 'message' => 'Club name is required.'];
    }

    if ($advisorName === '') {
        return ['success' => false, 'message' => 'Advisor name is required.'];
    }

    if ($maxCapacity <= 0) {
        return ['success' => false, 'message' => 'Maximum capacity must be a positive number.'];
    }

    $updatedAt = date('Y-m-d H:i:s');

    $stmt = $conn->prepare(
        'UPDATE club SET Club_name = ?, Description = ?, advisorName = ?, clubStatus = ?, maxCapacity = ?, Updated_at = ?
         WHERE Club_id = ?'
    );

    if (!$stmt) {
        $stmt = $conn->prepare(
            'UPDATE club SET Club_name = ?, Description = ?, advisorName = ?, clubStatus = ?, maxCapacity = ?
             WHERE Club_id = ?'
        );

        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }

        $stmt->bind_param('ssssii', $clubName, $description, $advisorName, $status, $maxCapacity, $clubId);
    } else {
        $stmt->bind_param('ssssisi', $clubName, $description, $advisorName, $status, $maxCapacity, $updatedAt, $clubId);
    }

    if ($stmt->execute() && $stmt->affected_rows >= 0) {
        $stmt->close();

        return ['success' => true, 'message' => 'Club updated successfully.'];
    }

    $message = $stmt->error;
    $stmt->close();

    return ['success' => false, 'message' => 'Could not update club: ' . $message];
}

/**
 * Advisor text stored on the club row (if any).
 */
function clubAdvisorTextFromRow(array $row): string
{
    foreach (['advisorName', 'Advisor_name', 'Advisor', 'advisor_name'] as $key) {
        if (!empty($row[$key])) {
            return trim((string) $row[$key]);
        }
    }

    return '';
}

function clubDescriptionFromRow(array $row): string
{
    foreach (['Description', 'Club_description', 'description'] as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
            return trim((string) $row[$key]);
        }
    }

    return '';
}

function clubIsActiveFromRow(array $row): bool
{
    if (isset($row['Is_active'])) {
        return (int) $row['Is_active'] === 1;
    }

    if (isset($row['is_active'])) {
        return (int) $row['is_active'] === 1;
    }

    if (isset($row['Status'])) {
        return strcasecmp(trim((string) $row['Status']), 'inactive') !== 0;
    }

    if (isset($row['clubStatus'])) {
        $val = trim((string) $row['clubStatus']);
        if ($val === '1' || strcasecmp($val, 'active') === 0) {
            return true;
        }
        if ($val === '0' || strcasecmp($val, 'inactive') === 0) {
            return false;
        }

        return strcasecmp($val, 'inactive') !== 0;
    }

    return true;
}

function clubAdvisorUserIdFromRow(array $row): ?int
{
    foreach (['Advisor_User_id', 'advisor_user_id', 'User_id_advisor'] as $key) {
        if (!empty($row[$key])) {
            return (int) $row[$key];
        }
    }

    return null;
}

/**
 * Clubs for admin club management list, with optional filters (substring match).
 * Supports common column variants on `club` plus optional advisor FK to `user`.
 *
 * @return list<array{Club_id:int,Club_name:string,Description:string,Advisor_name:string,Is_active:int}>
 */
function getClubsForManagement(?string $searchFilter = null, ?string $searchKeyword = null): array
{
    global $conn;

    $result = $conn->query('SELECT * FROM club ORDER BY Club_name ASC');
    if ($result === false) {
        return [];
    }

    $rawRows = [];
    while ($row = $result->fetch_assoc()) {
        $rawRows[] = $row;
    }

    $advisorIds = [];
    foreach ($rawRows as $row) {
        if (clubAdvisorTextFromRow($row) !== '') {
            continue;
        }

        $aid = clubAdvisorUserIdFromRow($row);
        if ($aid !== null && $aid > 0) {
            $advisorIds[$aid] = true;
        }
    }

    $advisorNamesByUserId = [];
    if ($advisorIds !== []) {
        $ids = array_keys($advisorIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $conn->prepare(
            "SELECT User_id, FullName FROM user WHERE User_id IN ($placeholders)"
        );
        if ($stmt) {
            $stmt->bind_param($types, ...$ids);
            if ($stmt->execute()) {
                $ures = $stmt->get_result();
                if ($ures) {
                    while ($u = $ures->fetch_assoc()) {
                        $advisorNamesByUserId[(int) $u['User_id']] = (string) $u['FullName'];
                    }
                }
            }

            $stmt->close();
        }
    }

    $allowed = ['Club_name', 'Advisor_name'];
    $filter = trim((string) ($searchFilter ?? ''));
    $kw = trim((string) ($searchKeyword ?? ''));

    $clubKw = '';
    $advKw = '';
    if ($kw !== '' && in_array($filter, $allowed, true)) {
        if ($filter === 'Club_name') {
            $clubKw = $kw;
        } else {
            $advKw = $kw;
        }
    }

    $out = [];
    foreach ($rawRows as $row) {
        $advisor = clubAdvisorTextFromRow($row);
        if ($advisor === '') {
            $aid = clubAdvisorUserIdFromRow($row);
            if ($aid !== null && isset($advisorNamesByUserId[$aid])) {
                $advisor = $advisorNamesByUserId[$aid];
            }
        }

        $clubId = (int) ($row['Club_id'] ?? $row['club_id'] ?? 0);
        $clubName = (string) ($row['Club_name'] ?? '');

        if ($clubKw !== '' && stripos($clubName, $clubKw) === false) {
            continue;
        }

        if ($advKw !== '' && stripos($advisor, $advKw) === false) {
            continue;
        }

        $out[] = [
            'Club_id' => $clubId,
            'Club_name' => $clubName,
            'Description' => clubDescriptionFromRow($row),
            'Advisor_name' => $advisor,
            'maxCapacity' => clubMaxCapacityFromRow($row),
            'Is_active' => clubIsActiveFromRow($row) ? 1 : 0,
        ];
    }

    return $out;
}

/**
 * Delete a user and related club rows.
 *
 * @return array{success:bool,message:string}
 */
function deleteUser(int $userId): array
{
    global $conn;

    if ($userId <= 0) {
        return ['success' => false, 'message' => 'Invalid user ID.'];
    }

    $conn->begin_transaction();

    try {
        $related = [
            ['clubcommitee', 'User_id'],
            ['clubmember', 'User_id'],
        ];

        foreach ($related as [$table, $column]) {
            $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$column` = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed ($table): " . $conn->error);
            }

            $stmt->bind_param('i', $userId);
            if (!$stmt->execute()) {
                throw new Exception("Delete failed ($table): " . $stmt->error);
            }

            $stmt->close();
        }

        $stmt = $conn->prepare('DELETE FROM user WHERE User_id = ?');
        if (!$stmt) {
            throw new Exception('Prepare failed (user): ' . $conn->error);
        }

        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            throw new Exception('Delete failed (user): ' . $stmt->error);
        }

        if ($stmt->affected_rows < 1) {
            $stmt->close();
            throw new Exception('User not found.');
        }

        $stmt->close();
        $conn->commit();

        return ['success' => true, 'message' => 'User deleted successfully.'];
    } catch (Exception $e) {
        $conn->rollback();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Delete a club and related membership rows.
 *
 * @return array{success:bool,message:string}
 */
function deleteClub(int $clubId): array
{
    global $conn;

    if ($clubId <= 0) {
        return ['success' => false, 'message' => 'Invalid club ID.'];
    }

    $conn->begin_transaction();

    try {
        $related = [
            ['clubcommitee', 'Club_id'],
            ['clubmember', 'Club_id'],
        ];

        foreach ($related as [$table, $column]) {
            $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$column` = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed ($table): " . $conn->error);
            }

            $stmt->bind_param('i', $clubId);
            if (!$stmt->execute()) {
                throw new Exception("Delete failed ($table): " . $stmt->error);
            }

            $stmt->close();
        }

        $stmt = $conn->prepare('DELETE FROM club WHERE Club_id = ?');
        if (!$stmt) {
            throw new Exception('Prepare failed (club): ' . $conn->error);
        }

        $stmt->bind_param('i', $clubId);
        if (!$stmt->execute()) {
            throw new Exception('Delete failed (club): ' . $stmt->error);
        }

        if ($stmt->affected_rows < 1) {
            $stmt->close();
            throw new Exception('Club not found.');
        }

        $stmt->close();
        $conn->commit();

        return ['success' => true, 'message' => 'Club deleted successfully.'];
    } catch (Exception $e) {
        $conn->rollback();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getAssignedRoles()
{
    global $conn;

    $sql = "SELECT Committee_role_id, position AS Role_name FROM commiteerole ORDER BY position ASC";
    $result = $conn->query($sql);

    return $result;
}

/**
 * Committee members available for club assignment.
 */
function getCommitteeUsers()
{
    global $conn;

    $sql = "SELECT User_id, FullName, Student_id
            FROM user
            WHERE role = 'committee' AND Is_active = 1
            ORDER BY FullName ASC";

    return $conn->query($sql);
}

/**
 * Assign an existing committee user to a club.
 *
 * @return array{success:bool,message:string}
 */
function assignClubCommittee(int $userId, int $clubId, int $committeeRoleId, string $startDate = ''): array
{
    global $conn;

    if ($userId <= 0 || $clubId <= 0 || $committeeRoleId <= 0) {
        return ['success' => false, 'message' => 'Please select committee member, club, and role.'];
    }

    $startDate = trim($startDate);
    if ($startDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        return ['success' => false, 'message' => 'Please select a valid start date.'];
    }

    $stmt = $conn->prepare(
        "SELECT role FROM user WHERE User_id = ? LIMIT 1"
    );
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $userRow = $userResult ? $userResult->fetch_assoc() : null;
    $stmt->close();

    if (!$userRow || strtolower((string) $userRow['role']) !== 'committee') {
        return ['success' => false, 'message' => 'Selected user is not a committee member.'];
    }

    $stmt = $conn->prepare(
        'SELECT 1 FROM clubcommitee WHERE User_id = ? AND Club_id = ? LIMIT 1'
    );
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param('ii', $userId, $clubId);
    $stmt->execute();
    $exists = $stmt->get_result();
    $alreadyAssigned = $exists && $exists->num_rows > 0;
    $stmt->close();

    if ($alreadyAssigned) {
        return ['success' => false, 'message' => 'This member is already assigned to the selected club.'];
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare(
            'INSERT INTO clubcommitee (User_id, Club_id, Committee_role_id, Assigned_date) VALUES (?, ?, ?, ?)'
        );
        if (!$stmt) {
            throw new Exception('Prepare failed (committee): ' . $conn->error);
        }

        $stmt->bind_param('iiis', $userId, $clubId, $committeeRoleId, $startDate);
        if (!$stmt->execute()) {
            throw new Exception('Assign failed: ' . $stmt->error);
        }
        $stmt->close();

        $memberStatus = 'Active';

        $stmt = $conn->prepare(
            'SELECT 1 FROM clubmember WHERE User_id = ? AND Club_id = ? LIMIT 1'
        );
        if (!$stmt) {
            throw new Exception('Prepare failed (member check): ' . $conn->error);
        }

        $stmt->bind_param('ii', $userId, $clubId);
        $stmt->execute();
        $memberResult = $stmt->get_result();
        $memberExists = $memberResult && $memberResult->num_rows > 0;
        $stmt->close();

        if (!$memberExists) {
            $stmt = $conn->prepare(
                'INSERT INTO clubmember (User_id, Club_id, Joined_date, Status) VALUES (?, ?, ?, ?)'
            );
            if (!$stmt) {
                throw new Exception('Prepare failed (member): ' . $conn->error);
            }

            $stmt->bind_param('iiss', $userId, $clubId, $startDate, $memberStatus);
            if (!$stmt->execute()) {
                throw new Exception('Member record failed: ' . $stmt->error);
            }
            $stmt->close();
        }

        $conn->commit();

        return ['success' => true, 'message' => 'Club committee assigned successfully.'];
    } catch (Exception $e) {
        $conn->rollback();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Club details for a logged-in committee user (from their assignment).
 *
 * @return array<string,mixed>|null
 */
function getCommitteeClubForUser(int $userId): ?array
{
    global $conn;

    if ($userId <= 0) {
        return null;
    }

    $stmt = $conn->prepare(
        'SELECT c.*, cc.Assigned_date, cr.position AS Role_name
         FROM clubcommitee cc
         INNER JOIN club c ON cc.Club_id = c.Club_id
         LEFT JOIN commiteerole cr ON cc.Committee_role_id = cr.Committee_role_id
         WHERE cc.User_id = ?
         ORDER BY cc.Assigned_date DESC
         LIMIT 1'
    );

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $userId);
    if (!$stmt->execute()) {
        $stmt->close();

        return null;
    }

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return null;
    }

    $statusRaw = trim((string) ($row['clubStatus'] ?? ''));
    if ($statusRaw === '') {
        $statusRaw = clubIsActiveFromRow($row) ? 'Active' : 'Inactive';
    }

    return [
        'Club_id' => (int) ($row['Club_id'] ?? 0),
        'Club_name' => (string) ($row['Club_name'] ?? ''),
        'Description' => clubDescriptionFromRow($row),
        'Advisor_name' => clubAdvisorTextFromRow($row),
        'club_status' => $statusRaw,
        'Role_name' => (string) ($row['Role_name'] ?? ''),
        'Assigned_date' => (string) ($row['Assigned_date'] ?? ''),
    ];
}

/**
 * Update club description for a committee user assigned to that club.
 *
 * @return array{success:bool,message:string}
 */
function updateClubDescriptionByCommittee(int $userId, int $clubId, string $description): array
{
    global $conn;

    if ($userId <= 0 || $clubId <= 0) {
        return ['success' => false, 'message' => 'Invalid club or user.'];
    }

    $assignedClub = getCommitteeClubForUser($userId);
    if (!$assignedClub || (int) $assignedClub['Club_id'] !== $clubId) {
        return ['success' => false, 'message' => 'You are not allowed to edit this club.'];
    }

    $description = trim($description);
    $updatedAt = date('Y-m-d H:i:s');

    $stmt = $conn->prepare(
        'UPDATE club SET Description = ?, Updated_at = ? WHERE Club_id = ?'
    );

    if (!$stmt) {
        $stmt = $conn->prepare('UPDATE club SET Description = ? WHERE Club_id = ?');
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }

        $stmt->bind_param('si', $description, $clubId);
    } else {
        $stmt->bind_param('ssi', $description, $updatedAt, $clubId);
    }

    if ($stmt->execute()) {
        $stmt->close();

        return ['success' => true, 'message' => 'Club description updated successfully.'];
    }

    $message = $stmt->error;
    $stmt->close();

    return ['success' => false, 'message' => 'Could not update description: ' . $message];
}

/**
 * Whether a user is already a member of a club.
 */
function isUserClubMember(int $userId, int $clubId): bool
{
    global $conn;

    if ($userId <= 0 || $clubId <= 0) {
        return false;
    }

    $stmt = $conn->prepare(
        'SELECT 1 FROM clubmember WHERE User_id = ? AND Club_id = ? LIMIT 1'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ii', $userId, $clubId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result && $result->num_rows > 0;
    $stmt->close();

    return $exists;
}

/**
 * Club details for the student details page.
 *
 * @return array<string,mixed>|null
 */
function getClubDetailsForStudent(int $clubId, int $userId = 0): ?array
{
    global $conn;

    if ($clubId <= 0) {
        return null;
    }

    $stmt = $conn->prepare('SELECT * FROM club WHERE Club_id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $clubId);
    if (!$stmt->execute()) {
        $stmt->close();

        return null;
    }

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return null;
    }

    $statusRaw = trim((string) ($row['clubStatus'] ?? ''));
    if ($statusRaw === '') {
        $statusRaw = clubIsActiveFromRow($row) ? 'Active' : 'Inactive';
    }

    return [
        'Club_id' => (int) ($row['Club_id'] ?? 0),
        'Club_name' => (string) ($row['Club_name'] ?? ''),
        'Description' => clubDescriptionFromRow($row),
        'Advisor_name' => clubAdvisorTextFromRow($row),
        'club_status' => $statusRaw,
        'Is_active' => clubIsActiveFromRow($row) ? 1 : 0,
        'maxCapacity' => clubMaxCapacityFromRow($row),
        'is_member' => $userId > 0 && isUserClubMember($userId, $clubId),
    ];
}

/**
 * Student joins a club; remaining capacity on the club row is reduced by 1.
 *
 * @return array{success:bool,message:string}
 */
function joinClubAsStudent(int $userId, int $clubId): array
{
    global $conn;

    if ($userId <= 0 || $clubId <= 0) {
        return ['success' => false, 'message' => 'Invalid request.'];
    }

    $stmt = $conn->prepare(
        "SELECT role FROM user WHERE User_id = ? LIMIT 1"
    );
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $userRow = $userResult ? $userResult->fetch_assoc() : null;
    $stmt->close();

    if (!$userRow || strtolower((string) $userRow['role']) !== 'student') {
        return ['success' => false, 'message' => 'Only students can join clubs.'];
    }

    $club = getClubDetailsForStudent($clubId, $userId);
    if (!$club) {
        return ['success' => false, 'message' => 'Club not found.'];
    }

    if ((int) $club['Is_active'] !== 1) {
        return ['success' => false, 'message' => 'This club is not accepting members.'];
    }

    if ($club['is_member']) {
        return ['success' => false, 'message' => 'You are already a member of this club.'];
    }

    $capacity = $club['maxCapacity'];
    if ($capacity === null || $capacity <= 0) {
        return ['success' => false, 'message' => 'This club has no available slots.'];
    }

    $joinedDate = date('Y-m-d');
    $memberStatus = 'Active';

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare(
            'INSERT INTO clubmember (User_id, Club_id, Joined_date, Status) VALUES (?, ?, ?, ?)'
        );
        if (!$stmt) {
            $stmt = $conn->prepare(
                'INSERT INTO clubmember (User_id, Club_id, Joined_date, clubStatus) VALUES (?, ?, ?, ?)'
            );
        }
        if (!$stmt) {
            throw new Exception('Prepare failed (member): ' . $conn->error);
        }

        $stmt->bind_param('iiss', $userId, $clubId, $joinedDate, $memberStatus);
        if (!$stmt->execute()) {
            throw new Exception('Could not join club: ' . $stmt->error);
        }
        $stmt->close();

        $stmt = $conn->prepare(
            'UPDATE club SET maxCapacity = maxCapacity - 1 WHERE Club_id = ? AND maxCapacity > 0'
        );
        if (!$stmt) {
            throw new Exception('Prepare failed (capacity): ' . $conn->error);
        }

        $stmt->bind_param('i', $clubId);
        if (!$stmt->execute() || $stmt->affected_rows < 1) {
            throw new Exception('No available slots for this club.');
        }
        $stmt->close();

        $conn->commit();

        return ['success' => true, 'message' => 'You have successfully joined the club.'];
    } catch (Exception $e) {
        $conn->rollback();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * All members enrolled in a club (for committee club management view).
 *
 * @return list<array<string,mixed>>
 */
function getClubMembersByClubId(int $clubId): array
{
    global $conn;

    if ($clubId <= 0) {
        return [];
    }

    $sql = "
        SELECT u.User_id, u.FullName, u.Student_id, u.role, u.Email, u.Phone_num, cm.*
        FROM clubmember cm
        INNER JOIN user u ON cm.User_id = u.User_id
        WHERE cm.Club_id = ?
        ORDER BY u.FullName ASC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $clubId);
    if (!$stmt->execute()) {
        $stmt->close();

        return [];
    }

    $result = $stmt->get_result();
    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $memberStatus = '';
            foreach (['Status', 'clubStatus', 'status'] as $statusKey) {
                if (isset($row[$statusKey]) && $row[$statusKey] !== '') {
                    $memberStatus = (string) $row[$statusKey];
                    break;
                }
            }

            $rows[] = [
                'User_id' => (int) ($row['User_id'] ?? 0),
                'FullName' => (string) ($row['FullName'] ?? ''),
                'Student_id' => (string) ($row['Student_id'] ?? ''),
                'role' => (string) ($row['role'] ?? ''),
                'Email' => (string) ($row['Email'] ?? ''),
                'Phone_num' => (string) ($row['Phone_num'] ?? ''),
                'Joined_date' => (string) ($row['Joined_date'] ?? ''),
                'Member_status' => $memberStatus,
            ];
        }
    }

    $stmt->close();

    return $rows;
}

/**
 * All club committee assignments for listing.
 *
 * @return array<int,array<string,mixed>>
 */
function getClubCommitteeAssignments(): array
{
    global $conn;

    $sql = "
        SELECT
            cc.Club_committee_id,
            cc.User_id,
            cc.Club_id,
            cc.Committee_role_id,
            cc.Assigned_date,
            u.FullName,
            u.Student_id,
            c.Club_name,
            cr.position AS Role_name
        FROM clubcommitee cc
        INNER JOIN user u ON cc.User_id = u.User_id
        INNER JOIN club c ON cc.Club_id = c.Club_id
        INNER JOIN commiteerole cr ON cc.Committee_role_id = cr.Committee_role_id
        ORDER BY cc.Assigned_date DESC, c.Club_name ASC, u.FullName ASC
    ";

    $result = $conn->query($sql);
    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return $rows;
}

/**
 * Single club committee assignment by primary key.
 *
 * @return array<string,mixed>|null
 */
function getClubCommitteeById(int $clubCommitteeId): ?array
{
    global $conn;

    if ($clubCommitteeId <= 0) {
        return null;
    }

    $stmt = $conn->prepare(
        'SELECT Club_committee_id, User_id, Club_id, Committee_role_id, Assigned_date
         FROM clubcommitee WHERE Club_committee_id = ? LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $clubCommitteeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

/**
 * Update a club committee assignment.
 *
 * @return array{success:bool,message:string}
 */
function updateClubCommittee(
    int $clubCommitteeId,
    int $userId,
    int $clubId,
    int $committeeRoleId,
    string $startDate
): array {
    global $conn;

    if ($clubCommitteeId <= 0 || $userId <= 0 || $clubId <= 0 || $committeeRoleId <= 0) {
        return ['success' => false, 'message' => 'Please complete all fields.'];
    }

    $startDate = trim($startDate);
    if ($startDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        return ['success' => false, 'message' => 'Please select a valid start date.'];
    }

    $existing = getClubCommitteeById($clubCommitteeId);
    if (!$existing) {
        return ['success' => false, 'message' => 'Assignment not found.'];
    }

    $stmt = $conn->prepare(
        'SELECT 1 FROM clubcommitee
         WHERE User_id = ? AND Club_id = ? AND Club_committee_id <> ? LIMIT 1'
    );
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param('iii', $userId, $clubId, $clubCommitteeId);
    $stmt->execute();
    $dup = $stmt->get_result();
    $duplicate = $dup && $dup->num_rows > 0;
    $stmt->close();

    if ($duplicate) {
        return ['success' => false, 'message' => 'This member is already assigned to the selected club.'];
    }

    $oldUserId = (int) $existing['User_id'];
    $oldClubId = (int) $existing['Club_id'];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare(
            'UPDATE clubcommitee
             SET User_id = ?, Club_id = ?, Committee_role_id = ?, Assigned_date = ?
             WHERE Club_committee_id = ?'
        );
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $stmt->bind_param('iiisi', $userId, $clubId, $committeeRoleId, $startDate, $clubCommitteeId);
        if (!$stmt->execute()) {
            throw new Exception('Update failed: ' . $stmt->error);
        }
        $stmt->close();

        $stmt = $conn->prepare(
            'UPDATE clubmember SET Joined_date = ?
             WHERE User_id = ? AND Club_id = ?'
        );
        if ($stmt) {
            $stmt->bind_param('sii', $startDate, $oldUserId, $oldClubId);
            $stmt->execute();
            $stmt->close();
        }

        if ($oldUserId !== $userId || $oldClubId !== $clubId) {
            $stmt = $conn->prepare(
                'UPDATE clubmember SET User_id = ?, Club_id = ?, Joined_date = ?
                 WHERE User_id = ? AND Club_id = ?'
            );
            if ($stmt) {
                $stmt->bind_param('iisii', $userId, $clubId, $startDate, $oldUserId, $oldClubId);
                $stmt->execute();
                $stmt->close();
            }
        }

        $conn->commit();

        return ['success' => true, 'message' => 'Club committee assignment updated successfully.'];
    } catch (Exception $e) {
        $conn->rollback();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Delete a club committee assignment.
 *
 * @return array{success:bool,message:string}
 */
function deleteClubCommittee(int $clubCommitteeId): array
{
    global $conn;

    if ($clubCommitteeId <= 0) {
        return ['success' => false, 'message' => 'Invalid assignment ID.'];
    }

    $existing = getClubCommitteeById($clubCommitteeId);
    if (!$existing) {
        return ['success' => false, 'message' => 'Assignment not found.'];
    }

    $userId = (int) $existing['User_id'];
    $clubId = (int) $existing['Club_id'];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare(
            'DELETE FROM clubcommitee WHERE Club_committee_id = ?'
        );
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $stmt->bind_param('i', $clubCommitteeId);
        if (!$stmt->execute() || $stmt->affected_rows < 1) {
            throw new Exception('Delete failed or assignment not found.');
        }
        $stmt->close();

        $stmt = $conn->prepare(
            'DELETE FROM clubmember WHERE User_id = ? AND Club_id = ?'
        );
        if ($stmt) {
            $stmt->bind_param('ii', $userId, $clubId);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();

        return ['success' => true, 'message' => 'Club committee assignment deleted successfully.'];
    } catch (Exception $e) {
        $conn->rollback();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getUserList()
{
    global $conn;

    $sql = "SELECT User_id, FullName, Phone_num, Student_id, role, Is_active FROM user";
    $result = $conn->query($sql);
    return $result;
}

function search($filter = null, $keyword = null)
{
    global $conn;

    // Allowed columns for safety
    $allowed_filters = [
        'FullName',
        'User_id',
        'Phone_num',
        'Student_id'
    ];

    $sql = "SELECT User_id, FullName, Phone_num, Student_id, role, Is_active FROM user";
    $params = [];
    $types = '';

    if (
        !empty($filter) &&
        !empty($keyword) &&
        in_array($filter, $allowed_filters)
    ) {
        $sql .= " WHERE $filter LIKE ?";
        $params[] = '%' . $keyword . '%';
        $types = 's';
    }

    // Prepare the query
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed in search(): " . $conn->error);
    }

    // Bind parameters if searching
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    // Execute and get results
    if (!$stmt->execute()) {
        die("Execute failed in search(): " . $stmt->error);
    }

    return $stmt->get_result();
}

function loginUser($icNum, $password, $role)
{
    global $conn;

    $role = strtolower(trim($role));

    $stmt = $conn->prepare(
        "SELECT * FROM user WHERE icNum = ? AND LOWER(role) = ?"
    );

    if (!$stmt) {
        die($conn->error);
    }

    $stmt->bind_param(
        "ss",
        $icNum,
        $role
    );

    // Redirect page (under Module1/ role dashboards)
    if ($role === 'admin') {
        $redirect = 'Module1/admin.php';
    } elseif ($role === 'student') {
        $redirect = 'Module1/student.php';
    } elseif ($role === 'committee') {
        $redirect = 'Module1/committee.php';
    } else {
        $redirect = 'login.php';
    }

    if (!$stmt->execute()) {

        return [
            'success' => false,
            'message' => 'Database execution error: '
                . $stmt->error,
            'redirect' => null
        ];
    }

    $result = $stmt->get_result();

    if ($result && $user = $result->fetch_assoc()) {

        // Check active (support Is_active / is_active column names)
        $isActive = 1;
        if (isset($user['Is_active'])) {
            $isActive = (int) $user['Is_active'];
        } elseif (isset($user['is_active'])) {
            $isActive = (int) $user['is_active'];
        }

        if ($isActive !== 1) {
            return [
                'success' => false,
                'message' => 'Account inactive.',
                'redirect' => null
            ];
        }

        // Verify password
        if (
            password_verify(
                $password,
                $user['Password_hash']
            )
        ) {

            // Now also return FullName in the user array
            return [
                'success' => true,
                'message' => 'Login successful.',
                'redirect' => $redirect,
                'user' => [
                    'User_id' => $user['User_id'],
                    'icNum' => $user['icNum'],
                    'Student_id' => $user['Student_id'],
                    'role' => strtolower((string) $user['role']),
                    'FullName' => $user['FullName']
                ]
            ];
        } else {

            return [
                'success' => false,
                'message' => 'Invalid password.',
                'redirect' => null
            ];
        }
    } else {

        return [
            'success' => false,
            'message' => 'Invalid username or role.',
            'redirect' => null
        ];
    }
}

function edit($user_id)
{
    // Ensure $conn is available (may need to get from global or pass as argument)
    global $conn; // Add this if $conn is defined globally elsewhere

    $success = null;
    $error = null;
    $user = null;

    // Handle update request
    if (($_SERVER["REQUEST_METHOD"] ?? '') == "POST" && isset($_POST['User_id'])) {
        $userId = $_POST['User_id'];
        $fullName = $_POST['FullName'];
        $phoneNum = $_POST['Phone_num'];
        $studentId = $_POST['Student_id'];
        $role = $_POST['role'];
        $isActive = isset($_POST['Is_active']) ? 1 : 0;

        $sql = "UPDATE user SET FullName=?, Phone_num=?, Student_id=?, role=?, Is_active=?, Updated_at=? WHERE User_id=?";
        if ($stmt = $conn->prepare($sql)) {
            $updatedAt = date("Y-m-d H:i:s");
            $stmt->bind_param("ssssisi", $fullName, $phoneNum, $studentId, $role, $isActive, $updatedAt, $userId);
            if ($stmt->execute()) {
                $success = "User updated successfully.";
            } else {
                $error = "Failed to update user.";
            }
            $stmt->close();
        } else {
            $error = "Database error. Could not prepare statement.";
        }
    }

    // Retrieve user info for edit form (either via $_GET parameter or the $user_id passed in)
    $lookupId = null;
    if (isset($_GET['User_id'])) {
        $lookupId = $_GET['User_id'];
    } elseif (!empty($user_id)) {
        $lookupId = $user_id;
    }

    if ($lookupId !== null) {
        $sql = "SELECT * FROM user WHERE User_id=?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $lookupId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
            } else {
                $error = "User not found.";
            }
            $stmt->close();
        } else {
            $error = "Database error. Could not prepare statement.";
        }
    } else {
        $error = "No user selected for editing.";
    }

    // Return data for use in the page that called edit()
    return [
        "user" => $user,
        "success" => $success,
        "error" => $error
    ];
}

/**
 * Club summary counts for the admin dashboard.
 *
 * @return array{total_students:int,active_clubs:int,inactive_clubs:int,total_members:int}
 */
function getClubDashboardStats(): array
{
    global $conn;

    $totalClubs = 0;
    $activeClubs = 0;

    $clubResult = $conn->query('SELECT * FROM club');
    if ($clubResult) {
        while ($row = $clubResult->fetch_assoc()) {
            $totalClubs++;
            if (clubIsActiveFromRow($row)) {
                $activeClubs++;
            }
        }
    }

    $totalStudents = 0;
    $studentResult = $conn->query(
        "SELECT COUNT(*) AS cnt FROM user WHERE LOWER(role) = 'student'"
    );
    if ($studentResult) {
        $studentRow = $studentResult->fetch_assoc();
        $totalStudents = (int) ($studentRow['cnt'] ?? 0);
    }

    $totalMembers = 0;
    $memberResult = $conn->query('SELECT COUNT(*) AS cnt FROM clubmember');
    if ($memberResult) {
        $memberRow = $memberResult->fetch_assoc();
        $totalMembers = (int) ($memberRow['cnt'] ?? 0);
    }

    return [
        'total_students' => $totalStudents,
        'active_clubs' => $activeClubs,
        'inactive_clubs' => $totalClubs - $activeClubs,
        'total_members' => $totalMembers,
    ];
}

/**
 * Student member count per club for dashboard chart.
 *
 * @return list<array{Club_name:string,student_count:int}>
 */
function getStudentDistributionByClub(): array
{
    global $conn;

    $sql = "
        SELECT c.Club_name, COUNT(cm.User_id) AS student_count
        FROM club c
        LEFT JOIN clubmember cm ON c.Club_id = cm.Club_id
        LEFT JOIN user u ON cm.User_id = u.User_id AND LOWER(u.role) = 'student'
        GROUP BY c.Club_id, c.Club_name
        ORDER BY student_count DESC, c.Club_name ASC
    ";

    $result = $conn->query($sql);
    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            'Club_name' => (string) ($row['Club_name'] ?? ''),
            'student_count' => (int) ($row['student_count'] ?? 0),
        ];
    }

    return $rows;
}

/**
 * Club ID for a committee user (supports getClubIdByCommittee($userId) or ($conn, $userId)).
 */
function getClubIdByCommittee($userIdOrConn, $userId = null): ?int
{
    $uid = $userId !== null ? (int) $userId : (int) $userIdOrConn;
    $club = getCommitteeClubForUser($uid);

    return $club ? (int) $club['Club_id'] : null;
}

/**
 * Events for one club (mysqli_result).
 */
function getEventsByClub($clubIdOrConn, $clubId = null)
{
    global $conn;

    $id = $clubId !== null ? (int) $clubId : (int) $clubIdOrConn;
    if ($id <= 0) {
        return $conn->query('SELECT * FROM event WHERE 1 = 0');
    }

    $stmt = $conn->prepare(
        'SELECT * FROM event
         WHERE Club_id = ? AND Deleted_at IS NULL
         ORDER BY Event_Date DESC'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();

    return $stmt->get_result();
}

/**
 * All events (admin list).
 */
function getAllEvents($unused = null)
{
    global $conn;

    return $conn->query(
        'SELECT * FROM event
         WHERE Deleted_at IS NULL
         ORDER BY Event_Date DESC'
    );
}

function getEventById(int $eventId): ?array
{
    global $conn;

    if ($eventId <= 0) {
        return null;
    }

    $stmt = $conn->prepare(
        'SELECT * FROM event WHERE Event_id = ? AND Deleted_at IS NULL LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function countRegistrations(int $eventId): int
{
    global $conn;

    if ($eventId <= 0) {
        return 0;
    }

    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS cnt FROM event_registration
         WHERE Event_id = ? AND LOWER(Reg_Status) IN ('registered', 'completed')"
    );
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return (int) ($row['cnt'] ?? 0);
}

function getUpcomingEvents()
{
    global $conn;

    return $conn->query(
        "SELECT e.*, c.Club_name
         FROM event e
         INNER JOIN club c ON e.Club_id = c.Club_id
         WHERE e.Deleted_at IS NULL
           AND LOWER(e.Event_Status) = 'upcoming'
           AND e.Event_Date >= NOW()
         ORDER BY e.Event_Date ASC"
    );
}

function isRegistered(int $userId, int $eventId): bool
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT 1 FROM event_registration
         WHERE User_id = ? AND Event_id = ?
           AND LOWER(Reg_Status) = 'registered'
         LIMIT 1"
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ii', $userId, $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result && $result->num_rows > 0;
    $stmt->close();

    return $exists;
}

function isWaiting(int $userId, int $eventId): bool
{
    global $conn;

    $stmt = $conn->prepare(
        'SELECT 1 FROM waiting_list WHERE User_id = ? AND Event_id = ? LIMIT 1'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ii', $userId, $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result && $result->num_rows > 0;
    $stmt->close();

    return $exists;
}

function getAllClubs()
{
    global $conn;

    return $conn->query(
        'SELECT Club_id AS club_id, Club_name FROM club ORDER BY Club_name ASC'
    );
}

function createEvent(
    string $name,
    string $desc,
    string $date,
    string $venue,
    int $capacity,
    int $clubId,
    int $createdBy
): bool {
    global $conn;

    $stmt = $conn->prepare(
        "INSERT INTO event
        (Event_Name, EventDesc, Event_Date, Venue, Student_Capacity, Event_Status, Club_id, Created_by, Created_at)
        VALUES (?, ?, ?, ?, ?, 'Upcoming', ?, ?, NOW())"
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ssssiii', $name, $desc, $date, $venue, $capacity, $clubId, $createdBy);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function updateEvent(
    int $eventId,
    string $name,
    string $desc,
    string $date,
    string $venue,
    int $capacity,
    string $status
): bool {
    global $conn;

    $stmt = $conn->prepare(
        'UPDATE event
         SET Event_Name = ?, EventDesc = ?, Event_Date = ?, Venue = ?,
             Student_Capacity = ?, Event_Status = ?, Updated_at = NOW()
         WHERE Event_id = ? AND Deleted_at IS NULL'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ssssisi', $name, $desc, $date, $venue, $capacity, $status, $eventId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function deleteEvent(int $eventId): bool
{
    global $conn;

    $stmt = $conn->prepare(
        'UPDATE event SET Deleted_at = NOW() WHERE Event_id = ?'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $eventId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

/**
 * Register a student for an event (not the same as creating a user account).
 */
function registerUser(int $userId, int $eventId): bool
{
    return registerStudentForEvent($userId, $eventId);
}

function registerStudentForEvent(int $userId, int $eventId): bool
{
    global $conn;

    if ($userId <= 0 || $eventId <= 0) {
        return false;
    }

    $event = getEventById($eventId);
    if (!$event) {
        return false;
    }

    if (isRegistered($userId, $eventId) || isWaiting($userId, $eventId)) {
        return false;
    }

    if (countRegistrations($eventId) >= (int) $event['Student_Capacity']) {
        return false;
    }

    $stmt = $conn->prepare(
        "INSERT INTO event_registration (Reg_Date, Reg_Status, Event_id, User_id)
         VALUES (NOW(), 'registered', ?, ?)"
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ii', $eventId, $userId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function addToWaiting(int $userId, int $eventId): bool
{
    global $conn;

    if (isWaiting($userId, $eventId) || isRegistered($userId, $eventId)) {
        return false;
    }

    $stmt = $conn->prepare(
        'INSERT INTO waiting_list (Waiting_Date, Notified_Flag, Converted_Flag, Event_id, User_id)
         VALUES (NOW(), 0, 0, ?, ?)'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ii', $eventId, $userId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function getUserRegistrations(int $userId)
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT er.*, e.Event_Name, e.Event_Date
         FROM event_registration er
         INNER JOIN event e ON er.Event_id = e.Event_id
         WHERE er.User_id = ? AND e.Deleted_at IS NULL
         ORDER BY e.Event_Date DESC"
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();

    return $stmt->get_result();
}

/**
 * Student event registration history (for event list / my registrations UI).
 *
 * @return list<array<string,mixed>>
 */
function getStudentEventRegistrationHistory(int $userId): array
{
    global $conn;

    if ($userId <= 0) {
        return [];
    }

    $stmt = $conn->prepare(
        "SELECT er.Reg_Id, er.Reg_Status, er.Reg_Date, er.Check_In_Time, er.Point_Value,
                e.Event_id, e.Event_Name, e.Event_Date, e.Venue, e.Event_Status AS Event_lifecycle,
                c.Club_name
         FROM event_registration er
         INNER JOIN event e ON er.Event_id = e.Event_id
         INNER JOIN club c ON e.Club_id = c.Club_id
         WHERE er.User_id = ? AND e.Deleted_at IS NULL
         ORDER BY e.Event_Date DESC"
    );
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $userId);
    if (!$stmt->execute()) {
        $stmt->close();

        return [];
    }

    $result = $stmt->get_result();
    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    $stmt->close();

    return $rows;
}

function cancelRegistration(int $userId, int $eventId): bool
{
    global $conn;

    if ($userId <= 0 || $eventId <= 0) {
        return false;
    }

    $stmt = $conn->prepare(
        "UPDATE event_registration er
         INNER JOIN event e ON er.Event_id = e.Event_id
         SET er.Reg_Status = 'cancelled'
         WHERE er.User_id = ? AND er.Event_id = ?
           AND LOWER(er.Reg_Status) = 'registered'
           AND e.Event_Date >= NOW()
           AND e.Deleted_at IS NULL"
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ii', $userId, $eventId);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();

    return $ok;
}

/**
 * Event row with club name (for attendance / committee pages).
 *
 * @return array<string,mixed>|null
 */
function getEventWithClub(int $eventId): ?array
{
    global $conn;

    if ($eventId <= 0) {
        return null;
    }

    $stmt = $conn->prepare(
        'SELECT e.*, c.Club_name
         FROM event e
         INNER JOIN club c ON e.Club_id = c.Club_id
         WHERE e.Event_id = ? AND e.Deleted_at IS NULL
         LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

/**
 * Whether a committee user may manage attendance for an event.
 */
function committeeCanManageEvent(int $userId, int $eventId): bool
{
    $club = getCommitteeClubForUser($userId);
    if (!$club) {
        return false;
    }

    $event = getEventById($eventId);

    return $event && (int) $event['Club_id'] === (int) $club['Club_id'];
}

/**
 * Registered students for an event (attendance form).
 *
 * @return list<array{User_id:int,Student_id:string,display_name:string}>
 */
function getRegisteredStudentsForEvent(int $eventId): array
{
    global $conn;

    if ($eventId <= 0) {
        return [];
    }

    $stmt = $conn->prepare(
        "SELECT u.User_id, u.Student_id,
                TRIM(COALESCE(NULLIF(u.FullName, ''), u.Fullname, '')) AS display_name
         FROM event_registration er
         INNER JOIN user u ON er.User_id = u.User_id
         WHERE er.Event_id = ?
           AND LOWER(er.Reg_Status) IN ('registered', 'completed')
         ORDER BY display_name ASC"
    );
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $eventId);
    if (!$stmt->execute()) {
        $stmt->close();

        return [];
    }

    $result = $stmt->get_result();
    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = [
                'User_id' => (int) ($row['User_id'] ?? 0),
                'Student_id' => (string) ($row['Student_id'] ?? ''),
                'display_name' => (string) ($row['display_name'] ?? ''),
            ];
        }
    }
    $stmt->close();

    return $rows;
}

function calculateAttendancePoints(string $attendanceStatus, string $volunteerStatus): int
{
    $points = 0;

    if ($attendanceStatus === 'Present') {
        $points += 10;
    } elseif ($attendanceStatus === 'Late') {
        $points += 5;
    } elseif ($attendanceStatus === 'Absent') {
        $points -= 10;
    }

    if ($volunteerStatus === 'Yes') {
        $points += 5;
    }

    return $points;
}

/**
 * Insert a row into the Module 4 attendance table.
 */
function insertEventAttendanceRecord(
    int $eventId,
    string $studentName,
    string $studentId,
    string $clubName,
    string $eventName,
    string $attendanceDate,
    string $attendanceTime,
    string $attendanceStatus,
    string $volunteerStatus
): bool {
    global $conn;

    $points = calculateAttendancePoints($attendanceStatus, $volunteerStatus);

    $stmt = $conn->prepare(
        'INSERT INTO attendance
        (event_id, student_name, student_id, club_name, event_name,
         attendance_date, attendance_time, attendance_status, volunteer_status, points)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        'issssssssi',
        $eventId,
        $studentName,
        $studentId,
        $clubName,
        $eventName,
        $attendanceDate,
        $attendanceTime,
        $attendanceStatus,
        $volunteerStatus,
        $points
    );
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

/**
 * Attendance records for one event.
 */
function getAttendanceByEventId(int $eventId)
{
    global $conn;

    if ($eventId <= 0) {
        return false;
    }

    $stmt = $conn->prepare(
        'SELECT * FROM attendance
         WHERE event_id = ?
         ORDER BY attendance_date DESC, attendance_time DESC'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $eventId);
    $stmt->execute();

    return $stmt->get_result();
}

/**
 * Attendance records for one club event.
 */
function getAttendanceByEvent(string $clubName, string $eventName)
{
    global $conn;

    $stmt = $conn->prepare(
        'SELECT * FROM attendance
         WHERE club_name = ? AND event_name = ?
         ORDER BY attendance_date DESC, attendance_time DESC'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $clubName, $eventName);
    $stmt->execute();

    return $stmt->get_result();
}

function eventsPerClub()
{
    global $conn;

    return $conn->query(
        "SELECT c.Club_name, COUNT(e.Event_id) AS total
         FROM club c
         LEFT JOIN event e ON c.Club_id = e.Club_id AND e.Deleted_at IS NULL
         GROUP BY c.Club_id, c.Club_name
         ORDER BY total DESC"
    );
}

function participantsPerEvent()
{
    global $conn;

    return $conn->query(
        "SELECT e.Event_Name, COUNT(er.Reg_Id) AS participants
         FROM event e
         LEFT JOIN event_registration er ON e.Event_id = er.Event_id
           AND LOWER(er.Reg_Status) IN ('registered', 'completed')
         WHERE e.Deleted_at IS NULL
         GROUP BY e.Event_id, e.Event_Name
         ORDER BY participants DESC"
    );
}

function monthlyTrend()
{
    global $conn;

    return $conn->query(
        "SELECT DATE_FORMAT(Created_at, '%Y-%m') AS month, COUNT(*) AS total
         FROM event
         WHERE Deleted_at IS NULL
         GROUP BY month
         ORDER BY month ASC"
    );
}

function countUser($conn)
{
    $sql_month = "SELECT DATE_FORMAT(Created_at, '%Y-%m') AS month, COUNT(User_id) AS user_count 
                  FROM user 
                  GROUP BY month 
                  ORDER BY month ASC";
    $result_month = $conn->query($sql_month);
    $data_month = [];
    if ($result_month) {
        while ($row = $result_month->fetch_assoc()) {
            $data_month[] = $row;
        }
    }

    $sql_week = "SELECT YEAR(Created_at) AS year, 
                        WEEK(Created_at, 1) AS week, 
                        MIN(DATE(Created_at) - INTERVAL (WEEKDAY(Created_at)) DAY) AS week_start,
                        MAX(DATE(Created_at) + INTERVAL (6 - WEEKDAY(Created_at)) DAY) AS week_end,
                        COUNT(User_id) AS user_count
                 FROM user
                 GROUP BY year, week
                 ORDER BY year ASC, week ASC";
    $result_week = $conn->query($sql_week);
    $data_week = [];
    if ($result_week) {
        while ($row = $result_week->fetch_assoc()) {
            $data_week[] = $row;
        }
    }

    return [
        "by_month" => $data_month,
        "by_week" => $data_week
    ];
}

function profile($conn, $user_id)
{
    $profile = null;

    // USER INFO
    $sql = "
        SELECT
            User_id,
            Student_id,
            FullName,
            icNum,
            role,
            Profile_photo,
            Phone_num,
            Email
        FROM user
        WHERE User_id = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        return null;
    }

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $profile = $result->fetch_assoc();

    $stmt->close();

    // COMMITTEE INFO
    if (
        $profile
        &&
        $profile['role'] == 'committee'
    ) {

        $sql_committee = "

            SELECT
                c.Club_name,
                cr.Role_name

            FROM clubcommittee cc

            LEFT JOIN clubs c
                ON cc.Club_id = c.Club_id

            LEFT JOIN committee_roles cr
                ON cc.Committee_role_id =
                cr.Committee_role_id

            WHERE cc.User_id = ?

        ";

        $stmt2 =
            $conn->prepare($sql_committee);

        if ($stmt2) {

            $stmt2->bind_param(
                "i",
                $user_id
            );

            $stmt2->execute();

            $result2 =
                $stmt2->get_result();

            $committee_info = [];

            while (
                $row =
                $result2->fetch_assoc()
            ) {

                $committee_info[] = $row;
            }

            $profile['committee_info']
                = $committee_info;

            $stmt2->close();
        }
    }

    // CLUB MEMBERSHIP
    $sql_member = "

        SELECT
            c.Club_name,
            cm.Joined_date,
            cm.status

        FROM clubmember cm

        LEFT JOIN clubs c
            ON cm.Club_id = c.Club_id

        WHERE cm.User_id = ?
        AND cm.status = 'active'

    ";

    $stmt3 =
        $conn->prepare($sql_member);

    if ($stmt3) {

        $stmt3->bind_param(
            "i",
            $user_id
        );

        $stmt3->execute();

        $result3 =
            $stmt3->get_result();

        $clubmember_info = [];

        while (
            $row =
            $result3->fetch_assoc()
        ) {

            $clubmember_info[] = $row;
        }

        $profile['clubmember_info']
            = $clubmember_info;

        $stmt3->close();
    }

    return $profile;
}

function editProfile($conn, $user_id, $data, $file = null)
{
    if (
        empty($user_id)
        ||
        !is_array($data)
    ) {

        return [
            'success' => false,
            'message' => 'Invalid parameters.'
        ];
    }

    // GET CURRENT USER
    $sql =
        "SELECT role, Profile_photo
        FROM user
        WHERE User_id = ?";

    $stmt =
        $conn->prepare($sql);

    if (!$stmt) {

        return [
            'success' => false,
            'message' =>
            'Database error: '
                . $conn->error
        ];
    }

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    $user =
        $result->fetch_assoc();

    $stmt->close();

    if (!$user) {

        return [
            'success' => false,
            'message' => 'User not found.'
        ];
    }

    $role = $user['role'];

    // COMMITTEE INFO
    if ($role === 'committee') {

        $sql_committee = "

        SELECT
            c.Club_name,
            cr.Role_name

        FROM clubcommittee cc

        LEFT JOIN clubs c
            ON cc.Club_id = c.Club_id

        LEFT JOIN committee_roles cr
            ON cc.Committee_role_id =
            cr.Committee_role_id

        WHERE cc.User_id = ?

    ";

        $stmt2 =
            $conn->prepare($sql_committee);

        if ($stmt2) {

            $stmt2->bind_param(
                "i",
                $user_id
            );

            $stmt2->execute();

            $result2 =
                $stmt2->get_result();

            if (
                $committee =
                $result2->fetch_assoc()
            ) {

                $user['Club_name'] =
                    $committee['Club_name'];

                $user['Position'] =
                    $committee['Role_name'];
            }

            $stmt2->close();
        }
    }

    // ALLOWED USER FIELDS
    $allowed_fields = [
        'FullName',
        'Email',
        'Phone_num'
    ];

    $fields = [];

    $types = '';

    $values = [];

    foreach ($data as $field => $value) {

        if (
            in_array(
                $field,
                $allowed_fields
            )
        ) {

            $fields[] =
                "$field = ?";

            $types .= 's';

            $values[] = trim($value);
        }
    }

    // PROFILE PHOTO
    if (
        $file
        &&
        isset($file['tmp_name'])
        &&
        !empty($file['tmp_name'])
    ) {

        $upload_dir =
            "UPLOAD/profile/";

        if (!is_dir($upload_dir)) {

            mkdir(
                $upload_dir,
                0777,
                true
            );
        }

        $filename =
            time()
            . "_"
            . basename(
                $file['name']
            );

        $target =
            $upload_dir
            . $filename;

        if (
            move_uploaded_file(
                $file['tmp_name'],
                $target
            )
        ) {

            $fields[] =
                "Profile_photo = ?";

            $types .= 's';

            $values[] = $target;
        }
    }

    if (empty($fields)) {

        return [
            'success' => false,
            'message' =>
            'No valid fields to update.'
        ];
    }

    $sql = "

        UPDATE user

        SET
            " . implode(', ', $fields) . "

        WHERE User_id = ?

    ";

    $stmt =
        $conn->prepare($sql);

    if (!$stmt) {

        return [
            'success' => false,
            'message' =>
            'Prepare failed: '
                . $conn->error
        ];
    }

    $types .= 'i';

    $values[] = $user_id;

    $stmt->bind_param(
        $types,
        ...$values
    );

    if ($stmt->execute()) {

        $stmt->close();

        return [
            'success' => true,
            'message' =>
            'Profile updated successfully.'
        ];
    } else {

        return [
            'success' => false,
            'message' =>
            'Execution failed: '
                . $stmt->error
        ];
    }
}
