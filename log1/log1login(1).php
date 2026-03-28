<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Logistic 1 Login</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT role FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role = $row['role'];

        if ($role == 'log1Admin') {
            header("Location: log1Admin.php");
            exit();
        } elseif ($role == 'log1Main') {
            header("Location: log1main.php");
            exit();
        } else {
            $error = "Invalid role.";
        }
    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
}
$conn->close();
?>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins', sans-serif;
}

body{
height:100vh;
display:flex;
}

/* LEFT SIDE BACKGROUND */

.left-panel{
flex:1;
background:linear-gradient(135deg,#1e2532,#1e2532);
position:relative;
overflow:hidden;
}

/* floating circles */

.left-panel::before,
.left-panel::after{
content:"";
position:absolute;
border-radius:50%;
background:rgba(255,255,255,0.08);
}

.left-panel::before{
width:350px;
height:350px;
top:-80px;
left:-80px;
}

.left-panel::after{
width:300px;
height:300px;
bottom:-100px;
left:50px;
}

/* RIGHT SIDE */

.right-panel{
flex:1;
display:flex;
align-items:center;
justify-content:center;
background:linear-gradient(135deg,#1e2532,#1e2532);
}

/* LOGIN CARD */

.login-card{

width:360px;

border-radius:20px;
overflow:hidden;

box-shadow:0 20px 40px rgba(0,0,0,0.3);

background:white;
}

/* CARD HEADER */

.card-header{

background:linear-gradient(90deg,#1e2532,#1e2532);
color:white;

padding:30px;
text-align:center;
}

.card-header h2{
margin-top:8px;
font-weight:600;
}

.card-header p{
font-size:13px;
opacity:0.8;
}

/* CARD BODY */

.card-body{
padding:30px;
}

/* INPUT */

.form-group{
margin-bottom:18px;
}

.form-group label{
font-size:13px;
font-weight:500;
display:block;
margin-bottom:6px;
}

.form-group input{

width:100%;
padding:10px 12px;

border-radius:8px;
border:1px solid #ccc;

outline:none;
font-size:14px;

transition:0.3s;
}

.form-group input:focus{
border-color:#1e2532;
box-shadow:0 0 5px rgba(18,162,79,0.4);
}

/* BUTTON */

.login-btn{

width:100%;
padding:12px;

border:none;
border-radius:10px;

background:linear-gradient(90deg,#1e2532,#1e2532);
color:white;

font-weight:600;
font-size:15px;

cursor:pointer;
transition:0.3s;
}

.login-btn:hover{
transform:scale(1.03);
box-shadow:0 10px 20px rgba(0,0,0,0.2);
}

/* FOOTER */

.card-footer{
text-align:center;
padding:15px;
font-size:12px;
color:#666;
background:#f5f5f5;
}

.card-footer i{
color:#1e2532;
margin-right:5px;
}

</style>
</head>

<body>

<div class="left-panel"></div>

<div class="right-panel">

<div class="login-card">

<div class="card-header">
<i class="fa-solid fa-briefcase fa-2x"></i>
<h2>Logistic 1</h2>
<p>Department Management System</p>
</div>

<div class="card-body">

<?php if (isset($error)): ?>
    <p style="color: red; text-align: center;"><?php echo $error; ?></p>
<?php endif; ?>

<form action="" method="POST">

<div class="form-group">
<label><i class="fa-solid fa-envelope"></i> Email Address</label>
<input type="email" name="email" placeholder="you@example.com" required>
</div>

<div class="form-group">
<label><i class="fa-solid fa-lock"></i> Password</label>
<input type="password" name="password" placeholder="Enter your password" required>
</div>

<button class="login-btn">
<i class="fa-solid fa-right-to-bracket"></i> Sign In
</button>

</form>

</div>

<div class="card-footer">
<i class="fa-solid fa-shield-halved"></i> Secure login with encryption
</div>

</div>

</div>

</body>
</html>
```
