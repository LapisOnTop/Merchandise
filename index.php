<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Mcores Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins', sans-serif;
}

body{
background:linear-gradient(135deg,#e0e0e0,#cfcfcf);
min-height:100vh;
display:flex;
flex-direction:column;
}

/* HEADER */

header{
background:linear-gradient(90deg,#1e2532,#1e2532);
color:white;
padding:22px 40px;
font-size:22px;
font-weight:600;
box-shadow:0 5px 15px rgba(0,0,0,0.2);
}

/* GRID */

.dashboard{
display:grid;
grid-template-columns:repeat(5,1fr);
gap:45px;
padding:60px 80px;
flex:1;
}

/* CARD */

.card{
background:linear-gradient(145deg,#1e2532,#1e2532);
color:white;
text-decoration:none;
height:180px;
border-radius:25px;
display:flex;
flex-direction:column;
align-items:center;
justify-content:center;
font-size:20px;
font-weight:600;
transition:all 0.35s ease;
box-shadow:0 10px 20px rgba(0,0,0,0.25);
position:relative;
overflow:visible;
}

.card i{
font-size:40px;
margin-bottom:15px;
}

/* HOVER CARD */

.card:hover{
transform:translateY(-10px) scale(1.05);
box-shadow:0 20px 35px rgba(0,0,0,0.35);
background:linear-gradient(145deg,#1e2532,#6f7378);
}

/* TOOLTIP */

.tooltip{

position:absolute;

top:180px;

left:50%;
transform:translateX(-50%);

width:260px;

background:white;
color:#333;

padding:15px;

border-radius:12px;

box-shadow:0 15px 30px rgba(0,0,0,0.25);

font-size:13px;

opacity:0;
pointer-events:none;

transition:0.3s;

max-height:180px;
overflow:auto;

z-index:10;
}

/* show tooltip */

.card:hover .tooltip{
opacity:1;
}

/* tooltip title */

.tooltip strong{
display:block;
margin-bottom:6px;
color:#1e2532;
}

/* tooltip list */

.tooltip ul{
padding-left:15px;
}

.tooltip li{
margin-bottom:4px;
}

/* FOOTER */

footer{
background:#1e2532;
color:white;
text-align:center;
padding:15px;
font-size:14px;
box-shadow:0 -3px 10px rgba(0,0,0,0.2);
}

footer span{
opacity:0.8;
}

</style>
</head>

<body>

<header>
<h1><i class="fa-solid fa-chart-line"></i> Mcores Dashboard</h1>
</header>

<div class="dashboard">

<a href="/hr1/hr1login.php" class="card">
<i class="fa-solid fa-users"></i>
<span>HR1</span>

<div class="tooltip">
<strong>Human Resource 1</strong>
</div>

</a>

<a href="/hr2/hr2login.php" class="card">
<i class="fa-solid fa-user-group"></i>
<span>HR2</span>

<div class="tooltip">
<strong>Human Resource 2</strong>

</div>

</a>

<a href="/hr3/hr3login.php" class="card">
<i class="fa-solid fa-id-badge"></i>
<span>HR3</span>

<div class="tooltip">
<strong>Human Resource 3</strong>

</div>

</a>

<a href="/hr4/hr4login.php" class="card">
<i class="fa-solid fa-user-check"></i>
<span>HR4</span>

<div class="tooltip">
<strong>Human Resource 4</strong>

</div>

</a>

<a href="/administrative/adminLogin.php" class="card">
<i class="fa-solid fa-building"></i>
<span>Administrative</span>

<div class="tooltip">
<strong>Administrative</strong>

</div>

</a>

<a href="/core1/core1login.php" class="card">
<i class="fa-solid fa-microchip"></i>
<span>CORE I</span>

<div class="tooltip">
<strong>Core Transaction 1</strong>

</div>

</a>

<a href="/core2/core2login.php" class="card">
<i class="fa-solid fa-gears"></i>
<span>CORE 2</span>

<div class="tooltip">
<strong>Core Transaction 2</strong>

</div>

</a>

<a href="/log1/log1login.php" class="card">
<i class="fa-solid fa-truck"></i>
<span>Logistic 1</span>

<div class="tooltip">
<strong>Logistics 1</strong>
</div>

</a>

<a href="/log2/log2login.php" class="card">
<i class="fa-solid fa-warehouse"></i>
<span>Logistic 2</span>

<div class="tooltip">
<strong>Logistics 2</strong>
</div>

</a>

<a href="/finance/financeLogin.php" class="card">
<i class="fa-solid fa-coins"></i>
<span>Finance</span>

<div class="tooltip">
<strong>Financials</strong>
</div>

</a>

</div>

<footer>
© 2026 Mcores System Dashboard <span>| All Departments Management</span>
<li><a href="core2/core2pos.php">Previous</a></li>
</footer>

</body>
</html>
