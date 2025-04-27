<!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="footer-logo">
                        <img src="/images/logo.png" alt="متجر مشهور">
                        <h5>متجر مشهور</h5>
                    </div>
                    <p class="footer-desc">نقدم لك أفضل الخدمات لزيادة المتابعين والتفاعل على مواقع التواصل الاجتماعي بأسعار مناسبة وجودة عالية.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-telegram"></i></a>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <h5 class="footer-title">روابط سريعة</h5>
                    <ul class="footer-links">
                        <li><a href="/">الرئيسية</a></li>
                        <li><a href="/top">الأكثر مبيعاً</a></li>
                        <li><a href="/about-us.php">من نحن</a></li>
                        <li><a href="/faq.php">الأسئلة الشائعة</a></li>
                        <li><a href="/contact.php">اتصل بنا</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3 mb-4">
                    <h5 class="footer-title">الخدمات</h5>
                    <ul class="footer-links">
                        <li><a href="/services/face">فيسبوك</a></li>
                        <li><a href="/services/insta">انستجرام</a></li>
                        <li><a href="/services/tiktok">تيك توك</a></li>
                        <li><a href="/services/youtube">يوتيوب</a></li>
                        <li><a href="/services/x">تويتر</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h5 class="footer-title">الدعم</h5>
                    <ul class="footer-links">
                        <li><a href="/terms.php">الشروط والأحكام</a></li>
                        <li><a href="/privacy.php">سياسة الخصوصية</a></li>
                        <li><a href="/refund.php">سياسة الاسترجاع</a></li>
                        <li><a href="/support.php">الدعم الفني</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md-6">
                        <p class="copyright">&copy; <?php echo date('Y'); ?> متجر مشهور. جميع الحقوق محفوظة.</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="payment-methods">
                            <img src="/images/visa.png" alt="Visa">
                            <img src="/images/mastercard.png" alt="MasterCard">
                            <img src="/images/paypal.png" alt="PayPal">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        .footer {
            background-color: #fff;
            color: var(--text-color);
            padding: 50px 0 20px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .footer-logo img {
            max-height: 40px;
            margin-left: 10px;
        }
        
        .footer-logo h5 {
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .footer-desc {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .footer-social {
            display: flex;
            gap: 10px;
        }
        
        .footer-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #f0f0f0;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .footer-social a:hover {
            background-color: var(--primary-color);
            color: #fff;
            transform: translateY(-3px);
        }
        
        .footer-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
            padding-right: 5px;
        }
        
        .footer-bottom {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .copyright {
            font-size: 0.85rem;
            color: #777;
            margin-bottom: 0;
        }
        
        .payment-methods {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .payment-methods img {
            height: 24px;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        
        .payment-methods img:hover {
            opacity: 1;
        }
        
        @media (max-width: 767px) {
            .footer-bottom .text-end {
                text-align: center !important;
                margin-top: 15px;
            }
            
            .payment-methods {
                justify-content: center;
            }
            
            .copyright {
                text-align: center;
            }
        }
    </style>
</body>
</html>