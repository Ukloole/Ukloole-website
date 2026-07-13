<?php if(!defined('UKLOOLE')) die(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= h($metaDesc ?? 'Tech and Talent For The Modern Business. Complete customer service without the complexity.') ?>">
    <title><?= h(($pageTitle ?? '') ? $pageTitle.' — '.SITE_NAME : SITE_NAME.' — Tech and Talent For The Modern Business') ?></title>
    <link rel="icon" href="/assets/images/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if(!empty($extraHead)) echo $extraHead; ?>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-custom" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="/assets/images/logo.png" alt="Ukloole" height="40" class="me-2">
            <span class="brand-name">Ukloole</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="/tools.php">Tools</a></li>
                <li class="nav-item"><a class="nav-link" href="/blog.php">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="/careers.php">Careers</a></li>
                <li class="nav-item"><a class="nav-link" href="/contact.php">Contact</a></li>
                <li class="nav-item ms-2">
                    <a class="nav-link nav-link-hub" href="https://learn.ukloole.com" target="_blank">Learning Hub ↗</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-primary-custom" href="https://calendly.com/admin-ukloole/ukloole-early-access" target="_blank">Book a Call</a>
                </li>
            </ul>
        </div>
    </div>
</nav>