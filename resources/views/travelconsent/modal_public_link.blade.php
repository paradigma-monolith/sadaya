<!-- Modal Pop-up Link Publik Hasil Dokumen -->
<div id="modalPublicLink" style="display: none; position: fixed; inset: 0; z-index: 99999; align-items: center; justify-content: center; padding: 16px; background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; text-align: center; padding: 28px 24px; position: relative;">
        
        <!-- Close Button -->
        <button type="button" onclick="closePublicModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 4px 8px; border-radius: 8px; line-height: 1;" title="Tutup">
            &times;
        </button>

        <!-- Success Icon -->
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #ecfdf5; border: 2px solid #a7f3d0; color: #059669; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <!-- Title & Subtitle -->
        <h3 id="modalPublicTitle" style="margin: 0 0 6px 0; font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">
            Formulir Berhasil Disubmit!
        </h3>
        <p style="margin: 0 0 16px 0; font-size: 13px; color: #64748b; line-height: 1.5;">
            Dokumen Anda telah tercatat secara resmi. Simpan atau bagikan tautan publik berikut untuk melihat hasil dokumen kapan saja:
        </p>

        <!-- Registration Badge -->
        <div style="display: inline-flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 6px 14px; border-radius: 9999px; font-family: monospace; font-size: 13px; font-weight: 700; margin-bottom: 20px;">
            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span>
            <span id="modalPublicRegNo">REG-XXXXXXXX</span>
        </div>

        <!-- Link Copy Field Box -->
        <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 14px; padding: 12px; margin-bottom: 20px; text-align: left;">
            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
                🔗 Tautan Publik Hasil Dokumen:
            </label>
            <div style="display: flex; gap: 8px; align-items: center;">
                <input 
                    type="text" 
                    id="modalPublicUrlInput" 
                    readonly 
                    style="flex: 1; min-width: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 9px 12px; font-size: 12.5px; font-family: monospace; color: #1e293b; outline: none;" 
                    onclick="this.select()"
                >
                <button 
                    type="button" 
                    id="btnModalCopy" 
                    onclick="copyPublicModalLink()" 
                    style="background: #0f172a; color: #ffffff; border: none; border-radius: 8px; padding: 9px 16px; font-size: 12.5px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; white-space: nowrap; shrink: 0;"
                >
                    <svg id="iconCopyState" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <span id="textCopyState">Salin Link</span>
                </button>
            </div>
            <div id="copySuccessFeedback" style="display: none; font-size: 11px; font-weight: 600; color: #16a34a; margin-top: 6px;">
                ✓ Tautan publik berhasil disalin ke clipboard!
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <a 
                id="modalPublicViewBtn" 
                href="#" 
                target="_blank" 
                style="flex: 1; min-width: 150px; background: #0284c7; color: #ffffff; text-decoration: none; padding: 11px 18px; border-radius: 10px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                <span>Lihat Hasil Dokumen</span>
            </a>
            <button 
                type="button" 
                onclick="closePublicModal()" 
                style="flex: 1; min-width: 120px; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 11px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer;"
            >
                Tutup
            </button>
        </div>

    </div>
</div>

<script>
    function showPublicModal(data) {
        var modal = document.getElementById('modalPublicLink');
        if (!modal) return;
        
        if (data.regNo) {
            document.getElementById('modalPublicRegNo').textContent = data.regNo;
        }
        if (data.title) {
            document.getElementById('modalPublicTitle').textContent = data.title;
        }
        if (data.publicUrl) {
            document.getElementById('modalPublicUrlInput').value = data.publicUrl;
            document.getElementById('modalPublicViewBtn').href = data.publicUrl;
        }
        
        // Reset copy button state
        resetCopyButton();
        
        modal.style.display = 'flex';
    }

    function closePublicModal() {
        var modal = document.getElementById('modalPublicLink');
        if (modal) modal.style.display = 'none';
    }

    function resetCopyButton() {
        var btn = document.getElementById('btnModalCopy');
        var text = document.getElementById('textCopyState');
        var feedback = document.getElementById('copySuccessFeedback');
        if (btn) btn.style.backgroundColor = '#0f172a';
        if (text) text.textContent = 'Salin Link';
        if (feedback) feedback.style.display = 'none';
    }

    function copyPublicModalLink() {
        var input = document.getElementById('modalPublicUrlInput');
        if (!input || !input.value) return;

        var url = input.value;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(onCopySuccess).catch(function() {
                fallbackCopy(input);
            });
        } else {
            fallbackCopy(input);
        }
    }

    function fallbackCopy(input) {
        input.select();
        input.setSelectionRange(0, 99999);
        try {
            document.execCommand('copy');
            onCopySuccess();
        } catch (err) {
            alert('Silakan salin tautan secara manual: ' + input.value);
        }
    }

    function onCopySuccess() {
        var btn = document.getElementById('btnModalCopy');
        var text = document.getElementById('textCopyState');
        var feedback = document.getElementById('copySuccessFeedback');
        
        if (btn) btn.style.backgroundColor = '#16a34a';
        if (text) text.textContent = 'Tersalin! ✓';
        if (feedback) feedback.style.display = 'block';

        setTimeout(function() {
            resetCopyButton();
        }, 3000);
    }
</script>
