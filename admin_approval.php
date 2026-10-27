```php
<?php
session_start();
require_once "db.php";

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit();
}

$message = "";
$messageType = "";

// Approve or reject an employee
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employeeId = filter_input(
        INPUT_POST,
        "employee_id",
        FILTER_VALIDATE_INT
    );

    $action = $_POST["action"] ?? "";

    if (!$employeeId || !in_array($action, ["approve", "reject"], true)) {
        $message = "Invalid request.";
        $messageType = "error";
    } else {

        $newStatus = ($action === "approve")
            ? "approved"
            : "rejected";

        // Only employee accounts can be approved or rejected
        $stmt = $conn->prepare(
            "UPDATE users
             SET status = ?
             WHERE id = ? AND role = 'employee'"
        );

        if (!$stmt) {
            $message = "Database error. Please try again.";
            $messageType = "error";
        } else {
            $stmt->bind_param("si", $newStatus, $employeeId);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $message = ($action === "approve")
                    ? "Employee approved successfully!"
                    : "Employee rejected successfully.";

                $messageType = "success";
            } else {
                $message = "No employee was updated. Check the employee status or ID.";
                $messageType = "error";
            }

            $stmt->close();
        }
    }
}

// Get employees waiting for approval
$pendingEmployees = $conn->query(
    "SELECT id, name, email, phone, department
     FROM users
     WHERE role = 'employee' AND status = 'pending'
     ORDER BY id DESC"
);

// Get already processed employees
$processedEmployees = $conn->query(
    "SELECT id, name, email, phone, department, status
     FROM users
     WHERE role = 'employee'
       AND status IN ('approved', 'rejected')
     ORDER BY id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approval | Text &amp; Solution</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #172033;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 6%;
            background: white;
            border-bottom: 1px solid #e5eaf1;
        }

        .brand h2 {
            margin: 0 0 5px;
            color: #1d4ed8;
        }

        .brand p {
            margin: 0;
            color: #718096;
            font-size: 13px;
        }

        nav {
            display: flex;
            gap: 18px;
            align-items: center;
            flex-wrap: wrap;
        }

        nav a {
            color: #344054;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        nav a:hover { color: #2563eb; }

        main {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-heading { margin-bottom: 25px; }

        .page-heading h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .page-heading p {
            margin: 0;
            color: #718096;
            line-height: 1.6;
        }

        .message {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 22px;
        }

        .success {
            color: #166534;
            background: #e9f9ef;
            border: 1px solid #b7ebc6;
        }

        .error {
            color: #b91c1c;
            background: #fff0f0;
            border: 1px solid #fecaca;
        }

        .panel {
            background: white;
            border: 1px solid #e5eaf1;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 28px;
            box-shadow: 0 10px 30px rgba(20,40,80,.05);
        }

        .panel h2 {
            margin: 0 0 20px;
            font-size: 21px;
        }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th, td {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid #edf0f4;
            font-size: 14px;
        }

        th {
            background: #f5f7fb;
            color: #344054;
        }

        td { color: #475467; }

        .person {
            color: #172033;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .actions form { margin: 0; }

        button {
            border: 0;
            border-radius: 7px;
            padding: 9px 12px;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .approve { background: #16a34a; }
        .approve:hover { background: #15803d; }

        .reject { background: #dc2626; }
        .reject:hover { background: #b91c1c; }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status.approved {
            background: #dcfce7;
            color: #166534;
        }

        .status.rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty {
            text-align: center;
            color: #718096;
            padding: 28px 10px;
        }

        @media (max-width: 650px) {
            header {
                align-items: flex-start;
                flex-direction: column;
            }

            main { margin-top: 25px; }

            .panel { padding: 16px; }

            .page-heading h1 { font-size: 25px; }
        }
    </style>
</head>

<body>
<header>
    <div class="brand">
        <h2>Text &amp; Solution</h2>
        <p>Employee Management System</p>
    </div>

    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="admin_approval.php">Employee Approvals</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <div class="page-heading">
        <h1>Employee Approval Center</h1>
        <p>
            Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>.
            Review employee registrations and approve or reject requests.
        </p>
    </div>

    <?php if ($message !== ""): ?>
        <div class="message <?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <section class="panel">
        <h2>Pending Employee Requests</h2>

        <?php if (!$pendingEmployees): ?>
            <p class="empty">Unable to load employee requests.</p>
        <?php elseif ($pendingEmployees->num_rows === 0): ?>
            <p class="empty">No pending employee registrations.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($employee = $pendingEmployees->fetch_assoc()): ?>
                        <tr>
                            <td class="person">
                                <?php echo htmlspecialchars($employee["name"]); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($employee["email"]); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($employee["phone"]); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($employee["department"]); ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <form method="POST">
                                        <input type="hidden" name="employee_id"
                                               value="<?php echo (int)$employee["id"]; ?>">
                                        <button class="approve" name="action"
                                                value="approve" type="submit">
                                            Approve
                                        </button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="employee_id"
                                               value="<?php echo (int)$employee["id"]; ?>">
                                        <button class="reject" name="action"
                                                value="reject" type="submit">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>Processed Employee Requests</h2>

        <?php if (!$processedEmployees): ?>
            <p class="empty">Unable to load employee records.</p>
        <?php elseif ($processedEmployees->num_rows === 0): ?>
            <p class="empty">No approved or rejected employees yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($employee = $processedEmployees->fetch_assoc()): ?>
                        <tr>
                            <td class="person">
                                <?php echo htmlspecialchars($employee["name"]); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($employee["email"]); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($employee["department"]); ?>
                            </td>
                            <td>
                                <span class="status <?php echo htmlspecialchars($employee["status"]); ?>">
                                    <?php echo htmlspecialchars($employee["status"]); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
```