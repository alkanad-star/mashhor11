<?php
// footer.php
?>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="footer-about">
                    <img src="https://mashhor.shop/images/logo.png" alt="متجر مشهور" class="footer-logo">
                    <p class="footer-description">
                        نقدم لك خدمات التواصل الاجتماعي لتعزيز تواجدك بين منافسيك، أسهل وأبسط وأسرع خدمات سوشيال ميديا في العالم العربي
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <h4 class="footer-title">روابط سريعة</h4>
                <ul class="footer-links">
                    <li><a href="/"><i class="fas fa-home"></i> الرئيسية</a></li>
                    <li><a href="/free-services"><i class="fas fa-gift"></i> الخدمات المجانية</a></li>
                    <li><a href="/blog"><i class="fas fa-blog"></i> المدونة</a></li>
                    <li><a href="/terms"><i class="fas fa-file-contract"></i> شروط الاستخدام</a></li>
                    <li><a href="/contact"><i class="fas fa-envelope"></i> تواصل معنا</a></li>
                    <li><a href="/api"><i class="fas fa-code"></i> API</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <h4 class="footer-title">مقالات مفيدة</h4>
                <ul class="footer-articles">
                    <li><a href="/article/1">أفضل وأرخص موقع زيادة متابعين تيك توك 2024</a></li>
                    <li><a href="/article/2">شراء مشاهدات تيك توك: كيف تحصل على حساب نشط على التيك توك؟</a></li>
                    <li><a href="/article/3">أفضل 10 مواقع لزيادة متابعين انستقرام حقيقيين مجاناً</a></li>
                    <li><a href="/article/4">طرق ومواقع زيادة متابعين انستقرام 2024 مجاناً</a></li>
                    <li><a href="/article/5">أسهل طريقة لشراء تقييمات جوجل (خرائط جوجل) حقيقيين 2024</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>جميع الحقوق محفوظة © 2024 <a href="/">متجر مشهور</a></p>
        </div>
    </div>
</footer>

<style>
    .footer {
        background: #1a1a1a;
        color: #ffffff;
        padding: 60px 0 30px;
    }
    
    .footer-logo {
        width: 160px;
        margin-bottom: 20px;
    }
    
    .footer-description {
        color: #cccccc;
        font-size: 0.95rem;
        line-height: 1.8;
        margin-bottom: 20px;
    }
    
    .footer-social {
        display: flex;
        gap: 10px;
    }
    
    .social-link {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .social-link:hover {
        background: var(--primary-color);
        color: #ffffff;
        transform: translateY(-3px);
    }
    
    .footer-title {
        color: #ffffff;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary-color);
        display: inline-block;
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
        color: #cccccc;
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .footer-links a:hover {
        color: var(--primary-color);
        padding-right: 10px;
    }
    
    .footer-links i {
        font-size: 0.9rem;
        width: 20px;
        text-align: center;
    }
    
    .footer-articles {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .footer-articles li {
        margin-bottom: 12px;
    }
    
    .footer-articles a {
        color: #cccccc;
        text-decoration: none;
        font-size: 0.9rem;
        line-height: 1.6;
        transition: all 0.3s ease;
        display: block;
    }
    
    .footer-articles a:hover {
        color: var(--primary-color);
    }
    
    .footer-bottom {
        text-align: center;
        padding-top: 30px;
        margin-top: 40px;
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    
    .footer-bottom p {
        color: #888888;
        font-size: 0.9rem;
        margin: 0;
    }
    
    .footer-bottom a {
        color: var(--primary-color);
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .footer-bottom a:hover {
        color: #ffffff;
    }
    
    @media (max-width: 767px) {
        .footer {
            padding: 40px 0 20px;
        }
        
        .footer-bottom {
            margin-top: 20px;
            padding-top: 20px;
        }
    }
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
    
    // Navbar scroll behavior
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            document.querySelector('.navbar').classList.add('navbar-scrolled');
        } else {
            document.querySelector('.navbar').classList.remove('navbar-scrolled');
        }
    });
</script>

</body>
</html>