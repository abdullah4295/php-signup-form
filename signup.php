<?php
$name    = '';
$age     = '';
$city    = '';
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $age  = trim($_POST['age']  ?? '');
    $city = trim($_POST['city'] ?? '');

    $errors = [];

    if ($name === '') {
        $errors['name'] = 'Name is required';
    }

    if ($city === '') {
        $errors['city'] = 'City is required';
    }

    $ageValue = ($age !== '') ? (int)$age : null;

    if (empty($errors)) {
        try {
            $dsn = 'mysql:host=localhost;port=3307;dbname=signup_db;charset=utf8mb4';
            $pdo = new PDO($dsn, 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql  = 'INSERT INTO users (name, age, city) VALUES (:name, :age, :city)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':age'  => $ageValue,
                ':city' => $city,
            ]);

            $success = 'Welcome ' . $name . ' from ' . $city . '!';
            $name = $age = $city = '';

        } catch (PDOException $e) {
            $errors['db'] = 'Database error: ' . $e->getMessage();
        }
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

        header('Content-Type: application/json');
        echo json_encode([
            'errors'  => $errors,
            'success' => $success ?: null,
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #a78bfa 0%, #6366f1 50%, #60a5fa 100%);
            padding: 40px 20px;
        }

        .wrapper { max-width: 540px; margin: 0 auto; }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: linear-gradient(135deg, #7c3aed, #6366f1, #3b82f6);
            padding: 28px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header h1 {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .card-header .icon {
            width: 46px; height: 46px;
            background: rgba(255,255,255,0.25);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }

        .card-body { padding: 32px; }

        .success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-left: 4px solid #22c55e;
            border-radius: 10px;
            padding: 14px 18px;
            color: #15803d;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success::before { content: '✅'; font-size: 1.1rem; }

        .db-error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            border-radius: 10px;
            padding: 14px 18px;
            color: #b91c1c;
            margin-bottom: 22px;
            font-size: 0.9rem;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            margin-top: 20px;
        }
        label:first-of-type { margin-top: 0; }
        .card-body form label:first-of-type { margin-top: 0; }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: 'Poppins', Arial, sans-serif;
            color: #111827;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #f9fafb;
        }
        input[type="text"]:focus,
        input[type="number"]:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.12);
            background: #fff;
        }
        input.has-error { border-color: #ef4444; background: #fff5f5; }

        .error {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #dc2626;
            font-size: 0.82rem;
            font-weight: 500;
            margin-top: 6px;
        }
        .error::before {
            content: '⚠';
            font-size: 0.9rem;
        }

        button[type="submit"] {
            width: 100%;
            margin-top: 28px;
            padding: 14px;
            background: linear-gradient(135deg, #7c3aed, #6366f1, #3b82f6);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Poppins', Arial, sans-serif;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: opacity 0.2s, transform 0.1s;
            box-shadow: 0 4px 15px rgba(99,102,241,0.4);
        }
        button[type="submit"]:hover  { opacity: 0.92; transform: translateY(-1px); }
        button[type="submit"]:active { transform: translateY(0); }

        #ajax-msg {
            margin-top: 16px;
            font-size: 0.9rem;
            font-weight: 500;
            min-height: 20px;
        }

        .table-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .table-card h2 {
            padding: 20px 28px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e1b4b;
            border-bottom: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead tr {
            background: linear-gradient(135deg, #4c1d95, #4338ca);
        }
        thead th {
            padding: 14px 20px;
            color: #fff;
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            text-align: left;
        }
        tbody tr { transition: background 0.15s; }
        tbody tr:nth-child(even) { background: #f5f3ff; }
        tbody tr:hover           { background: #ede9fe; }
        tbody td {
            padding: 13px 20px;
            color: #374151;
            font-size: 0.9rem;
            border-bottom: 1px solid #e5e7eb;
        }
        tbody tr:last-child td { border-bottom: none; }

        .empty {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="card">
        <div class="card-header">
            <h1>Create Account</h1>
            <div class="icon">👤</div>
        </div>

        <div class="card-body">
            <?php if (!empty($success)): ?>
                <div class="success">
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['db'])): ?>
                <div class="db-error"><?= htmlspecialchars($errors['db'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form id="signup-form" method="POST" action="signup.php" novalidate>
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name"
                       placeholder="e.g. Sara Hasan"
                       class="<?= !empty($errors['name']) ? 'has-error' : '' ?>"
                       value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['name'])): ?>
                    <p class="error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <label for="age">Age <span style="font-weight:400;color:#9ca3af;">(optional)</span></label>
                <input type="number" id="age" name="age" min="0" max="150"
                       placeholder="e.g. 25"
                       value="<?= htmlspecialchars($age ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <label for="city">City</label>
                <input type="text" id="city" name="city"
                       placeholder="e.g. Dhaka"
                       class="<?= !empty($errors['city']) ? 'has-error' : '' ?>"
                       value="<?= htmlspecialchars($city ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['city'])): ?>
                    <p class="error"><?= htmlspecialchars($errors['city'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <button type="submit">Sign Up</button>
            </form>

            <div id="ajax-msg"></div>
        </div>
    </div>

    <?php
    try {
        $pdo2 = new PDO('mysql:host=localhost;port=3307;dbname=signup_db;charset=utf8mb4', 'root', '');
        $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $rows = $pdo2->query('SELECT id, name, age, city FROM users ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="table-card">
        <h2>👥 Registered Users</h2>
        <?php if (!empty($rows)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>City</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id'],        ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['name'],      ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['age'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['city'],      ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="empty">No users yet. Be the first to sign up! 🎉</p>
        <?php endif; ?>
    </div>
    <?php
    } catch (PDOException $e) {
        echo '<p class="db-error">Could not load users: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    }
    ?>

</div>

<script>
const form = document.getElementById('signup-form');

form.addEventListener('submit', function(event) {
    event.preventDefault();

    const data = new FormData(form);

    fetch('signup.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(result) {
        const msgDiv = document.getElementById('ajax-msg');

        if (result.success) {
            msgDiv.style.color = 'green';
            msgDiv.textContent = result.success;
            form.reset();

            setTimeout(function() {
                location.reload();
            }, 2000);
        } else {
            let errorText = '';
            for (const field in result.errors) {
                errorText += result.errors[field] + '\n';
            }
            msgDiv.style.color = 'red';
            msgDiv.textContent = errorText.trim();
        }
    })
    .catch(function(error) {
        document.getElementById('ajax-msg').textContent = 'Network error: ' + error;
    });
});
</script>

</body>
</html>
