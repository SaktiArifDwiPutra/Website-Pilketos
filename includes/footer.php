</main> <style>
    /* CSS Footer Modern */
    .elegant-footer {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); /* Slate 900 ke Indigo 950 */
        color: #f8fafc;
        margin-top: 60px;
        border-top: 4px solid #4f46e5; /* Garis aksen atas */
    }

    .footer-content {
        padding: 60px 0 40px;
    }

    .footer-sections {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 40px;
    }

    .footer-section h4 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 24px;
        position: relative;
        padding-bottom: 12px;
        color: #e0e7ff; /* Putih kebiruan */
    }

    .footer-section h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, #4f46e5, #ec4899); /* Gradasi aksen kekinian */
        border-radius: 2px;
    }

    .footer-logo h3 {
        color: #818cf8; /* Soft Indigo */
        margin-bottom: 8px;
        font-size: 1.6rem;
        font-weight: 700;
    }

    .footer-logo p {
        color: #94a3b8;
        font-size: 0.95rem;
        margin-bottom: 16px;
        font-weight: 500;
    }

    .footer-description {
        line-height: 1.7;
        margin-bottom: 20px;
        color: #cbd5e1;
        font-size: 0.95rem;
    }

    .contact-info p {
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        color: #cbd5e1;
        font-size: 0.95rem;
    }

    .contact-info a {
        color: #cbd5e1;
        text-decoration: none;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .contact-info a:hover {
        color: #818cf8;
        transform: translateX(5px); /* Efek geser kanan saat di-hover */
    }

    .contact-info i {
        margin-right: 12px;
        color: #818cf8;
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
    }

    .footer-bottom {
        background-color: rgba(0, 0, 0, 0.2);
        padding: 24px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .footer-bottom-content {
        display: flex;
        justify-content: center; /* Di-center agar lebih rapi */
        align-items: center;
        text-align: center;
    }

    .footer-bottom-content p {
        margin: 0;
        color: #94a3b8;
        font-size: 0.9rem;
    }

    /* Tombol Back to Top Modern */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        border: none;
        border-radius: 12px; /* Dibuat kotak dengan sudut membulat (Squircle) */
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
    }

    .back-to-top.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .back-to-top:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.6);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .elegant-footer {
            margin-top: 40px;
        }
        
        .footer-sections {
            grid-template-columns: 1fr;
            text-align: center;
            gap: 30px;
        }

        .footer-section h4::after {
            left: 50%;
            transform: translateX(-50%);
        }

        .contact-info p, 
        .contact-info a {
            justify-content: center;
        }
        
        .contact-info a:hover {
            transform: translateY(-2px); /* Ubah efek hover menjadi ke atas di HP */
        }
    }
</style>

<footer class="elegant-footer">
    <div class="footer-content">
        <div class="container">
            <div class="footer-sections">
                <div class="footer-section">
                    <div class="footer-logo">
                        <h3>MPK SMKN 4 Padalarang</h3>
                        <p>Menjaga Kualitas Demokrasi Sekolah</p>
                    </div>
                    <p class="footer-description">
                        Majelis Perwakilan Kelas SMKN 4 Padalarang adalah organisasi siswa 
                        yang bertugas menyelenggarakan pemilihan ketua OSIS dan mewakili 
                        aspirasi seluruh siswa/i.
                    </p>
                </div>
                
                <div class="footer-section">
                    <h4>Hubungi Kami</h4>
                    <div class="contact-info">
                        <p><i class="fa-solid fa-map-location-dot"></i> Jl. U.suryadi No. 451, Padalarang</p>
                        <p><a href="https://www.instagram.com/mpknepal.id" target="_blank"><i class="fa-brands fa-instagram"></i> @mpknepal.id</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <p>&copy; <?= date('Y') ?> Majelis Perwakilan Kelas SMKN 4 Padalarang. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<button id="backToTop" class="back-to-top" aria-label="Kembali ke atas">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logic Back to Top
    const backToTopButton = document.getElementById('backToTop');
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    });
    
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>

</body>
</html>