<div class="form-row">
    <div class="form-group">
        <label class="form-label">Nama Alumni</label>
        <input type="text" name="nama" class="form-control" value="{{ old('nama', $alumni->nama ?? $defaultNama ?? '') }}" required>
    </div>
    <div class="form-group">
        <label class="form-label">NIM</label>
        <input type="text" name="nim" class="form-control" value="{{ old('nim', $alumni->nim ?? '') }}">
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">Program Studi</label>
        <input type="text" name="prodi" class="form-control" value="{{ old('prodi', $alumni->prodi ?? '') }}">
    </div>
    <div class="form-group">
        <label class="form-label">Status Kerja</label>
        <select name="status_kerja" class="form-control">
            <option value="">-- Pilih Status --</option>
            <option value="PNS" {{ (old('status_kerja', $alumni->status_kerja ?? '') == 'PNS') ? 'selected' : '' }}>PNS</option>
            <option value="Swasta" {{ (old('status_kerja', $alumni->status_kerja ?? '') == 'Swasta') ? 'selected' : '' }}>Swasta</option>
            <option value="Wirausaha" {{ (old('status_kerja', $alumni->status_kerja ?? '') == 'Wirausaha') ? 'selected' : '' }}>Wirausaha</option>
        </select>
    </div>
</div>

<div class="grid-2">
    <div class="form-section">
        <h4 style="margin-bottom: 12px; font-size: 14px; color: var(--accent-light);">📱 Sosial Media</h4>
        <div class="form-group">
            <label class="form-label">LinkedIn (URL)</label>
            <input type="url" name="linkedin" class="form-control" value="{{ old('linkedin', $alumni->linkedin ?? '') }}" placeholder="https://linkedin.com/in/...">
        </div>
        <div class="form-group">
            <label class="form-label">Instagram</label>
            <input type="text" name="instagram" class="form-control" value="{{ old('instagram', $alumni->instagram ?? '') }}" placeholder="@username">
        </div>
        <div class="form-group">
            <label class="form-label">Facebook</label>
            <input type="text" name="facebook" class="form-control" value="{{ old('facebook', $alumni->facebook ?? '') }}">
        </div>
        <div class="form-group">
            <label class="form-label">TikTok</label>
            <input type="text" name="tiktok" class="form-control" value="{{ old('tiktok', $alumni->tiktok ?? '') }}">
        </div>
    </div>

    <div class="form-section">
        <h4 style="margin-bottom: 12px; font-size: 14px; color: var(--accent-light);">📞 Kontak & Kantor</h4>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $alumni->email ?? '') }}">
        </div>
        <div class="form-group">
            <label class="form-label">No HP / WhatsApp</label>
            <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $alumni->no_hp ?? '') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Tempat Bekerja</label>
            <input type="text" name="tempat_kerja" class="form-control" value="{{ old('tempat_kerja', $alumni->tempat_kerja ?? '') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Posisi</label>
            <input type="text" name="posisi" class="form-control" value="{{ old('posisi', $alumni->posisi ?? '') }}">
        </div>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">Alamat Bekerja</label>
        <textarea name="alamat_kerja" class="form-control" rows="2">{{ old('alamat_kerja', $alumni->alamat_kerja ?? '') }}</textarea>
    </div>
    <div class="form-group">
        <label class="form-label">Sosmed Perusahaan</label>
        <input type="text" name="sosmed_perusahaan" class="form-control" value="{{ old('sosmed_perusahaan', $alumni->sosmed_perusahaan ?? '') }}">
    </div>
</div>
