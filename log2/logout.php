<?php
session_start();
session_unset();
session_destroy();

// Force redirect to the absolute root homepage to prevent URL stacking
header("Location: /index.php");
exit();
?>
