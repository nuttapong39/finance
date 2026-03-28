<?php
// main.php
require_once __DIR__ . '/config.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" type="image/x-icon" href="pic/fms.png" />
	<title>ระบบบริหารจัดการการเงินและบัญชี - MOPH</title>
	
	<!-- Fonts -->
	<link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

	<!-- Bootstrap 5 -->
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
	
	<!-- Bootstrap Icons -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
	<!-- MOPH Theme -->
	<link rel="stylesheet" href="css/theme.css">
	<link rel="stylesheet" href="css/moph-font.css">
	
	<!-- SweetAlert2 -->
	<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
	
	<!-- Animate.css -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

	<style>
		:root {
			/* MOPH Color Scheme */
			--moph-primary: #0B6E4F;
			--moph-secondary: #08A045;
			--moph-accent: #FFB81C;
			--moph-dark: #1a4d2e;
			--moph-light: #e8f5e9;
			
			/* Card Colors */
			--card-blue: #0d6efd;
			--card-green: #198754;
			--card-orange: #fd7e14;
			--card-red: #dc3545;
			
			/* Neutral Colors */
			--white: #ffffff;
			--gray-50: #f8f9fa;
			--gray-100: #f1f3f5;
			--gray-200: #e9ecef;
			--gray-300: #dee2e6;
			--gray-600: #6c757d;
			--gray-700: #495057;
			--gray-800: #343a40;
			
			/* Shadows */
			--shadow-sm: 0 2px 4px rgba(11, 110, 79, 0.08);
			--shadow-md: 0 4px 12px rgba(11, 110, 79, 0.12);
			--shadow-lg: 0 8px 24px rgba(11, 110, 79, 0.15);
			--shadow-card: 0 10px 30px rgba(0, 0, 0, 0.1);
		}

		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: 'Sarabun', -apple-system, BlinkMacSystemFont, sans-serif;
			background: linear-gradient(135deg, var(--moph-light) 0%, #ffffff 50%, var(--gray-50) 100%);
			min-height: 100vh;
			color: var(--gray-800);
		}

		/* Main Container */
		.main-container {
			padding: 24px;
			max-width: 1400px;
			margin: 0 auto;
		}

		/* Page Header */
		.page-header {
			background: linear-gradient(135deg, var(--moph-primary) 0%, var(--moph-secondary) 100%);
			border-radius: 20px;
			padding: 32px;
			margin-bottom: 32px;
			box-shadow: var(--shadow-card);
			position: relative;
			overflow: hidden;
		}

		.page-header::before {
			content: '';
			position: absolute;
			top: -50%;
			right: -10%;
			width: 400px;
			height: 400px;
			background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
			border-radius: 50%;
		}

		.page-header h1 {
			color: var(--white);
			font-size: 32px;
			font-weight: 700;
			margin: 0;
			position: relative;
			z-index: 1;
		}

		.page-header p {
			color: rgba(255, 255, 255, 0.9);
			font-size: 16px;
			margin: 8px 0 0;
			position: relative;
			z-index: 1;
		}

		.page-header .badge {
			background: rgba(255, 255, 255, 0.2);
			color: var(--white);
			padding: 8px 16px;
			border-radius: 20px;
			font-size: 14px;
			font-weight: 500;
			margin-top: 12px;
			display: inline-block;
		}

		/* Cards Grid */
		.cards-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 24px;
			margin-bottom: 32px;
		}

		/* Menu Card */
		.menu-card {
			background: var(--white);
			border-radius: 20px;
			overflow: hidden;
			box-shadow: var(--shadow-md);
			transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
			border: 2px solid transparent;
			position: relative;
			cursor: pointer;
			text-decoration: none;
			display: block;
		}

		.menu-card:hover {
			transform: translateY(-8px);
			box-shadow: var(--shadow-card);
			border-color: var(--moph-primary);
		}

		.menu-card::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: linear-gradient(135deg, transparent 0%, rgba(11, 110, 79, 0.03) 100%);
			opacity: 0;
			transition: opacity 0.3s ease;
		}

		.menu-card:hover::before {
			opacity: 1;
		}

		/* Card Icon Section */
		.card-icon-section {
			padding: 40px 24px;
			display: flex;
			align-items: center;
			justify-content: center;
			position: relative;
			overflow: hidden;
		}

		.card-icon-section::after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 0;
			right: 0;
			height: 4px;
			transition: all 0.3s ease;
		}

		/* Card Colors */
		.menu-card.card-blue .card-icon-section {
			background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%);
		}

		.menu-card.card-blue .card-icon-section::after {
			background: var(--card-blue);
		}

		.menu-card.card-green .card-icon-section {
			background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%);
		}

		.menu-card.card-green .card-icon-section::after {
			background: var(--card-green);
		}

		.menu-card.card-orange .card-icon-section {
			background: linear-gradient(135deg, rgba(253, 126, 20, 0.1) 0%, rgba(253, 126, 20, 0.05) 100%);
		}

		.menu-card.card-orange .card-icon-section::after {
			background: var(--card-orange);
		}

		.menu-card.card-red .card-icon-section {
			background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
		}

		.menu-card.card-red .card-icon-section::after {
			background: var(--card-red);
		}

		/* Card Icon */
		.card-icon-wrapper {
			width: 100px;
			height: 100px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 50%;
			background: var(--white);
			box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
			transition: all 0.3s ease;
			position: relative;
		}

		.menu-card:hover .card-icon-wrapper {
			transform: scale(1.1) rotate(5deg);
		}

		.card-icon-wrapper img {
			width: 60px;
			height: 60px;
			object-fit: contain;
		}

		/* Alternative: Use icon fonts if images not available */
		.card-icon-wrapper i {
			font-size: 48px;
		}

		.menu-card.card-blue .card-icon-wrapper i {
			color: var(--card-blue);
		}

		.menu-card.card-green .card-icon-wrapper i {
			color: var(--card-green);
		}

		.menu-card.card-orange .card-icon-wrapper i {
			color: var(--card-orange);
		}

		.menu-card.card-red .card-icon-wrapper i {
			color: var(--card-red);
		}

		/* Card Title Section */
		.card-title-section {
			padding: 24px;
			background: var(--white);
		}

		.card-title {
			font-size: 18px;
			font-weight: 700;
			color: var(--gray-800);
			margin: 0;
			text-align: center;
			transition: color 0.3s ease;
		}

		.menu-card:hover .card-title {
			color: var(--moph-primary);
		}

		.card-description {
			font-size: 14px;
			color: var(--gray-600);
			margin: 8px 0 0;
			text-align: center;
			opacity: 0;
			max-height: 0;
			overflow: hidden;
			transition: all 0.3s ease;
		}

		.menu-card:hover .card-description {
			opacity: 1;
			max-height: 50px;
		}

		/* Quick Stats Section */
		.quick-stats {
			background: var(--white);
			border-radius: 20px;
			padding: 32px;
			box-shadow: var(--shadow-md);
			margin-bottom: 32px;
		}

		.quick-stats h3 {
			font-size: 20px;
			font-weight: 700;
			color: var(--moph-dark);
			margin-bottom: 24px;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.stats-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 20px;
		}

		.stat-item {
			padding: 20px;
			background: var(--gray-50);
			border-radius: 12px;
			border-left: 4px solid var(--moph-primary);
			transition: all 0.3s ease;
		}

		.stat-item:hover {
			background: var(--moph-light);
			transform: translateX(4px);
		}

		.stat-label {
			font-size: 14px;
			color: var(--gray-600);
			margin-bottom: 4px;
		}

		.stat-value {
			font-size: 24px;
			font-weight: 700;
			color: var(--moph-primary);
		}

		/* Responsive Design */
		@media (max-width: 768px) {
			.main-container {
				padding: 16px;
			}

			.page-header {
				padding: 24px;
				border-radius: 16px;
			}

			.page-header h1 {
				font-size: 24px;
			}

			.page-header p {
				font-size: 14px;
			}

			.cards-grid {
				grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
				gap: 16px;
			}

			.card-icon-section {
				padding: 32px 20px;
			}

			.card-icon-wrapper {
				width: 80px;
				height: 80px;
			}

			.card-icon-wrapper img {
				width: 50px;
				height: 50px;
			}

			.quick-stats {
				padding: 24px;
			}

			.stats-grid {
				grid-template-columns: 1fr;
			}
		}

		@media (max-width: 576px) {
			.cards-grid {
				grid-template-columns: 1fr;
			}
		}

		/* Animation Classes */
		.animate-fade-in {
			animation: fadeIn 0.6s ease-out;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(20px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.animate-slide-in {
			animation: slideIn 0.8s ease-out;
		}

		@keyframes slideIn {
			from {
				opacity: 0;
				transform: translateX(-30px);
			}
			to {
				opacity: 1;
				transform: translateX(0);
			}
		}

		/* Loading Overlay */
		.loading-overlay {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(0, 0, 0, 0.5);
			z-index: 9999;
			align-items: center;
			justify-content: center;
		}

		.loading-overlay.active {
			display: flex;
		}

		.loading-spinner {
			width: 60px;
			height: 60px;
			border: 4px solid var(--white);
			border-top-color: var(--moph-primary);
			border-radius: 50%;
			animation: spin 1s linear infinite;
		}

		@keyframes spin {
			to { transform: rotate(360deg); }
		}

		/* SweetAlert2 Customization */
		.swal2-popup {
			border-radius: 20px;
			font-family: 'Sarabun', sans-serif;
		}

		.swal2-title {
			color: var(--gray-800);
			font-size: 24px;
			font-weight: 700;
		}

		.swal2-confirm {
			background: var(--moph-primary) !important;
			border-radius: 10px;
			padding: 10px 30px;
			font-weight: 600;
		}

		.swal2-cancel {
			border-radius: 10px;
			padding: 10px 30px;
		}
	</style>

	<script type="text/JavaScript">
		function copyit(field) {
			var temp = document.getElementById(field);
			temp.focus();
			temp.select();
			
			// Modern clipboard API
			if (navigator.clipboard) {
				navigator.clipboard.writeText(temp.value).then(function() {
					Swal.fire({
						icon: 'success',
						title: 'คัดลอกสำเร็จ',
						text: 'คัดลอกข้อมูลไปยังคลิปบอร์ดแล้ว',
						timer: 1500,
						showConfirmButton: false,
						toast: true,
						position: 'top-end'
					});
				});
			} else {
				// Fallback for older browsers
				try {
					var therange = temp.createTextRange();
					therange.execCommand("Copy");
					Swal.fire({
						icon: 'success',
						title: 'คัดลอกสำเร็จ',
						timer: 1500,
						showConfirmButton: false,
						toast: true,
						position: 'top-end'
					});
				} catch(err) {
					Swal.fire({
						icon: 'error',
						title: 'เกิดข้อผิดพลาด',
						text: 'ไม่สามารถคัดลอกข้อมูลได้'
					});
				}
			}
		}
	</script>
</head>

<body>
	<!-- Loading Overlay -->
	<div class="loading-overlay" id="loadingOverlay">
		<div class="loading-spinner"></div>
	</div>

	<!-- Header Menu -->
	<div class="menu">
		<?php include 'header.php';?>
	</div>

	<!-- Main Container -->
	<div class="main-container">
		<!-- Page Header -->
		<div class="page-header animate-fade-in">
			<h1 style="display:flex; align-items:center; gap:10px;">
				<span class="msi" style="font-size:32px;">grid_view</span> เมนูหลัก
			</h1>
			<p>ระบบบริหารจัดการการเงินและบัญชี</p>
			<span class="badge" style="margin-top:12px; display:inline-flex; align-items:center; gap:5px;">
				<span class="msi" style="font-size:15px;">verified</span> กระทรวงสาธารณสุข
			</span>
		</div>

		<!-- Quick Stats (Optional - can be removed if not needed) -->
		<!-- 
		<div class="quick-stats animate-slide-in">
			<h3><i class="bi bi-bar-chart-fill"></i> สถิติรวม</h3>
			<div class="stats-grid">
				<div class="stat-item">
					<div class="stat-label">รายการทั้งหมด</div>
					<div class="stat-value">0</div>
				</div>
				<div class="stat-item">
					<div class="stat-label">รออนุมัติ</div>
					<div class="stat-value">0</div>
				</div>
				<div class="stat-item">
					<div class="stat-label">อนุมัติแล้ว</div>
					<div class="stat-value">0</div>
				</div>
				<div class="stat-item">
					<div class="stat-label">เบิกจ่ายแล้ว</div>
					<div class="stat-value">0</div>
				</div>
			</div>
		</div>
		-->

		<!-- Menu Cards Grid -->
		<div class="cards-grid">
			<!-- Card 1: บันทึกรายการ -->
			<a href="accounting.php" class="menu-card card-blue animate-fade-in" onclick="showLoading(event)">
				<div class="card-icon-section">
					<div class="card-icon-wrapper">
						<img src="pic/1.png" alt="บันทึกรายการ">
						<!-- Alternative icon if image not available -->
						<!-- <i class="bi bi-journal-text"></i> -->
					</div>
				</div>
				<div class="card-title-section">
					<h2 class="card-title">บันทึกรายการ</h2>
					<p class="card-description">จัดการและบันทึกรายการทางบัญชี</p>
				</div>
			</a>

			<!-- Card 2: ขออนุมัติ/เบิกจ่าย -->
			<a href="receive.php" class="menu-card card-green animate-fade-in" style="animation-delay: 0.1s" onclick="showLoading(event)">
				<div class="card-icon-section">
					<div class="card-icon-wrapper">
						<img src="pic/2.png" alt="ขออนุมัติ/เบิกจ่าย">
						<!-- Alternative icon if image not available -->
						<!-- <i class="bi bi-cash-coin"></i> -->
					</div>
				</div>
				<div class="card-title-section">
					<h2 class="card-title">ขออนุมัติ/เบิกจ่าย</h2>
					<p class="card-description">ส่งคำขอและเบิกจ่ายเงิน</p>
				</div>
			</a>

			<!-- Card 3: แผนงานและรายงาน -->
			<a href="plan.php" class="menu-card card-orange animate-fade-in" style="animation-delay: 0.2s" onclick="showLoading(event)">
				<div class="card-icon-section">
					<div class="card-icon-wrapper">
						<img src="pic/3.png" alt="แผนงานและรายงาน">
						<!-- Alternative icon if image not available -->
						<!-- <i class="bi bi-clipboard-data"></i> -->
					</div>
				</div>
				<div class="card-title-section">
					<h2 class="card-title">แผนงานและรายงาน</h2>
					<p class="card-description">ดูแผนงานและออกรายงาน</p>
				</div>
			</a>

			<!-- Card 4: ตั้งค่าระบบ -->
			<a href="config1.php" class="menu-card card-red animate-fade-in" style="animation-delay: 0.3s" onclick="showLoading(event)">
				<div class="card-icon-section">
					<div class="card-icon-wrapper">
						<img src="pic/4.png" alt="ตั้งค่าระบบ">
						<!-- Alternative icon if image not available -->
						<!-- <i class="bi bi-gear-fill"></i> -->
					</div>
				</div>
				<div class="card-title-section">
					<h2 class="card-title">ตั้งค่าระบบ</h2>
					<p class="card-description">จัดการการตั้งค่าและผู้ใช้งาน</p>
				</div>
			</a>
		</div>
	</div>

	<!-- Scripts (jQuery, Bootstrap 5 JS, SweetAlert2 already loaded by header.php) -->

	<script>
		// Show loading overlay when clicking menu cards
		function showLoading(event) {
			const overlay = document.getElementById('loadingOverlay');
			overlay.classList.add('active');
			
			// If there's an error, hide the overlay after 3 seconds
			setTimeout(() => {
				overlay.classList.remove('active');
			}, 3000);
		}

		// Welcome message on page load (optional)
		document.addEventListener('DOMContentLoaded', function() {
			// Show welcome toast (can be disabled if not needed)
			/*
			Swal.fire({
				icon: 'success',
				title: 'ยินดีต้อนรับ',
				text: 'เข้าสู่ระบบสำเร็จ',
				timer: 2000,
				showConfirmButton: false,
				toast: true,
				position: 'top-end'
			});
			*/
		});

		// Add hover effect sound or haptic feedback (optional)
		const menuCards = document.querySelectorAll('.menu-card');
		menuCards.forEach(card => {
			card.addEventListener('mouseenter', function() {
				// Add custom hover effects here if needed
			});
		});

		// Keyboard navigation support
		document.addEventListener('keydown', function(e) {
			if (e.ctrlKey) {
				switch(e.key) {
					case '1':
						e.preventDefault();
						window.location.href = 'accounting.php';
						break;
					case '2':
						e.preventDefault();
						window.location.href = 'receive.php';
						break;
					case '3':
						e.preventDefault();
						window.location.href = 'plan.php';
						break;
					case '4':
						e.preventDefault();
						window.location.href = 'config1.php';
						break;
				}
			}
		});

		// Add confirmation before navigating to sensitive sections
		const sensitiveLinks = document.querySelectorAll('a[href="config1.php"]');
		sensitiveLinks.forEach(link => {
			link.addEventListener('click', function(e) {
				// Uncomment to add confirmation dialog
				/*
				e.preventDefault();
				Swal.fire({
					title: 'ยืนยันการเข้าถึง',
					text: 'คุณต้องการเข้าสู่หน้าตั้งค่าระบบใช่หรือไม่?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'ยืนยัน',
					cancelButtonText: 'ยกเลิก',
					confirmButtonColor: '#0B6E4F',
					cancelButtonColor: '#6c757d'
				}).then((result) => {
					if (result.isConfirmed) {
						showLoading();
						window.location.href = this.href;
					}
				});
				*/
			});
		});
	</script>
</body>
</html>