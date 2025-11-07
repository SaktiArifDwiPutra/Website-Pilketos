</main>

<style>
    .elegant-footer {
        background: linear-gradient(135deg, #2c3e50 0%, #1a2530 100%);
        color: #ecf0f1;
        margin-top: 50px;
    }

    .footer-content {
        padding: 50px 0 30px;
    }

    .footer-sections {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .footer-section h4 {
        font-size: 1.2rem;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
        color: #3498db;
    }

    .footer-section h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 2px;
        background-color: #3498db;
    }

    .footer-logo h3 {
        color: #3498db;
        margin-bottom: 5px;
        font-size: 1.5rem;
    }

    .footer-logo p {
        color: #95a5a6;
        font-style: italic;
        margin-bottom: 15px;
    }

    .footer-description {
        line-height: 1.6;
        margin-bottom: 20px;
        color: #bdc3c7;
    }

    .footer-links {
        list-style: none;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: #ecf0f1;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .footer-links a:hover {
        color: #3498db;
        transform: translateX(5px);
    }

    .contact-info p {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        color: #bdc3c7;
    }

    .contact-info a {
        color: #bdc3c7;
        text-decoration: none;
        display: flex;
        align-items: center;
        transition: color 0.3s ease;
    }

    .contact-info a:hover {
        color: #3498db;
    }

    .contact-info i {
        margin-right: 10px;
        color: #3498db;
        width: 20px;
    }

    .footer-bottom {
        background-color: rgba(0, 0, 0, 0.3);
        padding: 20px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .footer-bottom-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .legal-links {
        display: flex;
        gap: 20px;
    }

    .legal-links a {
        color: #95a5a6;
        text-decoration: none;
        transition: color 0.3s;
        font-size: 0.9rem;
    }

    .legal-links a:hover {
        color: #3498db;
    }

    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .back-to-top.show {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        background-color: #2980b9;
        transform: translateY(-3px);
    }

@media (max-width: 768px) {
    .footer-sections {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .footer-section {
        margin-bottom: 20px;
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

    .footer-links {
        padding: 0;
        text-align: center;
    }

    .footer-links li {
        display: inline-block;
        margin: 8px 10px;
    }

    .footer-links a {
        display: inline-block;
    }

    .contact-info p, 
    .contact-info a {
        justify-content: center;
        text-align: center;
    }

    .footer-bottom-content {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .legal-links {
        justify-content: center;
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
                        <p><i class="fas fa-map-marker-alt"></i> Jl. U.suryadi No. 451, Padalarang</p>
                        <p><a href="https://www.instagram.com/mpknepal.id?utm_source=ig_web_button_share_sheet&igsh=MXhwazRkZmVic2p0dg=="><i class="fab fa-instagram"></i> @mpknepal.id</a></p>
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

<button id="backToTop" class="back-to-top">
    <i class="fas fa-chevron-up"></i>
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