<?php
session_start();
require "db.php";

if (
    !isset($_SESSION["user_id"]) ||
    strtolower($_SESSION["role"]) !== "employee"
) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "request_details") {
        $check = $conn->prepare(
            "SELECT id FROM permission_requests
             WHERE requester_id = ? AND target_role = 'admin'
             AND status = 'approved' LIMIT 1"
        );
        $check->bind_param("i", $userId);
        $check->execute();
        $alreadyApproved = $check->get_result()->num_rows > 0;
        $check->close();

        if ($alreadyApproved) {
            $message = "You already have permission to view admin details.";
        } else {
            $check = $conn->prepare(
                "SELECT id FROM permission_requests
                 WHERE requester_id = ? AND target_role = 'admin'
                 AND status = 'pending' LIMIT 1"
            );
            $check->bind_param("i", $userId);
            $check->execute();
            $pending = $check->get_result()->num_rows > 0;
            $check->close();

            if ($pending) {
                $message = "Your request is already pending.";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO permission_requests
                    (requester_id, requester_role, target_role, status)
                    VALUES (?, 'employee', 'admin', 'pending')"
                );
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();
                $message = "Admin details request sent successfully.";
            }
        }
    }

    if (in_array($action, ["approve", "reject"], true)) {
        $requestId = (int) ($_POST["request_id"] ?? 0);
        $newStatus = $action === "approve" ? "approved" : "rejected";

        $stmt = $conn->prepare(
            "UPDATE permission_requests
             SET status = ?
             WHERE id = ? AND target_role = 'employee'
             AND requester_role = 'admin' AND status = 'pending'"
        );
        $stmt->bind_param("si", $newStatus, $requestId);
        $stmt->execute();
        $message = $stmt->affected_rows
            ? "Request " . $newStatus . " successfully."
            : "Request could not be updated.";
        $stmt->close();
    }
}

$stmt = $conn->prepare(
    "SELECT pr.id, u.name, u.email, pr.created_at
     FROM permission_requests pr
     JOIN users u ON u.id = pr.requester_id
     WHERE pr.target_role = 'employee'
       AND pr.requester_role = 'admin'
       AND pr.status = 'pending'
     ORDER BY pr.created_at DESC"
);
$stmt->execute();
$requests = $stmt->get_result();

$stmt = $conn->prepare(
    "SELECT id FROM permission_requests
     WHERE requester_id = ? AND target_role = 'admin'
       AND status = 'approved' LIMIT 1"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$canViewAdmins = $stmt->get_result()->num_rows > 0;
$stmt->close();

$admins = null;
if ($canViewAdmins) {
    $admins = $conn->query(
        "SELECT id, name, email, phone, department
         FROM users WHERE role = 'admin' ORDER BY id DESC"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Employee Dashboard | CTS Portal</title>
<link rel="stylesheet" href="style.css">
<style>
.permission-card{max-width:1000px;margin:30px auto;padding:25px;background:rgba(18,28,55,.92);border:1px solid #34456e;border-radius:18px;color:#fff}
.permission-card h2{margin-bottom:15px}
.permission-card p{line-height:1.6}
.status-message{padding:12px;margin:15px 0;background:#24395e;border-radius:10px}
.action-btn{border:0;border-radius:8px;padding:9px 14px;margin:4px;cursor:pointer;color:#fff;font-weight:bold}
.request-btn{background:#635bff}
.approve-btn{background:#159957}
.reject-btn{background:#d9485f}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{padding:12px;text-align:left;border-bottom:1px solid #394765;overflow-wrap:anywhere}
.table-wrap{overflow-x:auto}
</style>
</head>
<body>
<nav>
    <h2>👨‍💻 Employee Dashboard</h2>
    <div><a href="logout.php">Logout</a></div>
</nav>

<main class="dashboard">
    <h1>Welcome, <?= htmlspecialchars($_SESSION["name"] ?? "Employee") ?> 👋</h1>

    <?php if ($message !== ""): ?>
        <p class="status-message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <section class="permission-card">
        <h2>🔐 Admin Details Permission</h2>
        <?php if (!$canViewAdmins): ?>
            <p>Request permission from an admin to view admin details.</p>
            <form method="post">
                <input type="hidden" name="action" value="request_details">
                <button class="action-btn request-btn" type="submit">📩 Request Admin Details</button>
            </form>
            <p>Status: Your permission is not approved yet.</p>
        <?php else: ?>
            <p>✅ Permission approved. You can view admin details below.</p>
        <?php endif; ?>
    </section>

    <section class="permission-card">
        <h2>📩 Admin Requests to View Employee Details</h2>
        <?php if ($requests->num_rows > 0): ?>
            <div class="table-wrap">
            <table>
                <tr><th>Admin Name</th><th>Email</th><th>Requested</th><th>Action</th></tr>
                <?php while ($r = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($r["name"]) ?></td>
                    <td><?= htmlspecialchars($r["email"]) ?></td>
                    <td><?= htmlspecialchars($r["created_at"]) ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="request_id" value="<?= (int)$r["id"] ?>">
                            <button class="action-btn approve-btn" name="action" value="approve">Approve</button>
                            <button class="action-btn reject-btn" name="action" value="reject">Reject</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            </div>
        <?php else: ?>
            <p>No pending admin requests.</p>
        <?php endif; ?>
    </section>

    <?php if ($canViewAdmins && $admins): ?>
    <section class="permission-card">
        <h2>👥 Admin Details</h2>
        <div class="table-wrap">
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Department</th></tr>
            <?php while ($a = $admins->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$a["id"] ?></td>
                <td><?= htmlspecialchars($a["name"]) ?></td>
                <td><?= htmlspecialchars($a["email"]) ?></td>
                <td><?= htmlspecialchars($a["phone"] ?? "") ?></td>
                <td><?= htmlspecialchars($a["department"] ?? "") ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        </div>
    </section>
    <?php endif; ?>
</main>
</body>
</html>