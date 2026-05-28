<?php if (current_user()): ?>
        <footer style="margin-top:44px;padding:16px 0 0;border-top:1px solid rgba(255,255,255,.35);display:flex;align-items:center;justify-content:space-between;gap:12px;color:var(--gray);font-size:12px;font-weight:600;">
            <span style="display:flex;align-items:center;gap:8px;">
                <img src="/logo.png" alt="SIGEA" style="width:20px;height:20px;object-fit:contain;opacity:.7;border-radius:4px;">
                SIGEA &copy; <?= date('Y') ?>
            </span>
            <span style="opacity:.75;">Sistema de Generacion y Evaluacion Automatica</span>
        </footer>
    </main>
</div>
<?php endif; ?>
<script src="/assets/js/app.js"></script>
</body>
</html>
