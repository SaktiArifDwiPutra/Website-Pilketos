</main>

<style>
    /* CSS Footer Modern - Tema Admin */
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
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
    }

    .footer-section h4 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 24px;
        position: relative;
        padding-bottom: 12px;
        color: #e0e7ff;
    }

    .footer-section h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, #4f46e5, #ec4899);
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
        font-style: italic;
    }

    .footer-description {
        line-height: 1.7;
        margin-bottom: 20px;
        color: #cbd5e1;
        font-size: 0.95rem;
    }

    /* Styling Tautan Cepat Admin */
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: #cbd5e1;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        font-size: 0.95rem;
    }

    .footer-links a::before {
        content: '\f105'; /* Icon chevron-right dari FontAwesome */
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        margin-right: 8px;
        color: #4f46e5;
        transition: transform 0.3s ease;
    }

    .footer-links a:hover {
        color: #818cf8;
        transform: translateX(5px);
    }
    
    .footer-links a:hover::before {
        transform: translateX(3px);
    }

    /* Styling Kontak */
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
        transform: translateX(5px);
    }

    .contact-info i {
        margin-right: 12px;
        color: #818cf8;
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
    }

    /* Footer Bottom */
    .footer-bottom {
        background-color: rgba(0, 0, 0, 0.2);
        padding: 24px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .footer-bottom-content {
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .footer-bottom-content p {
        margin: 0;
        color: #94a3b8;
        font-size: 0.9rem;
    }

    /* Tombol Back to Top Modern (Squircle) */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        border: none;
        border-radius: 12px;
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

        .footer-logo h3,
        .footer-logo p,
        .footer-description {
            text-align: center;
        }

        /* Tautan Cepat di HP dibuat berjajar menyamping seperti chips/pill */
        .footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .footer-links li {
            margin: 0;
        }

        .footer-links a {
            background: rgba(255,255,255,0.05);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .footer-links a::before {
            display: none; /* Sembunyikan ikon panah di mode pill */
        }

        .footer-links a:hover {
            background: rgba(129, 140, 248, 0.2);
            transform: translateY(-2px);
        }

        .contact-info p, 
        .contact-info a {
            justify-content: center;
        }

        .contact-info a:hover {
            transform: translateY(-2px);
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
                        aspirasi siswa.
                    </p>
                </div>
                
                <div class="footer-section">
                    <h4>Tautan Cepat</h4>
                    <ul class="footer-links">
                        <li><a href="../index.php">Dashboard</a></li>
                        <li><a href="tambah_calon.php">Tambah Calon</a></li>
                        <li><a href="tambah_akun.php">Tambah Akun</a></li>
                        <li><a href="hasil.php">Hasil Pemilihan</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Kontak Kami</h4>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var backToTopButton = document.getElementById('backToTop');
    
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