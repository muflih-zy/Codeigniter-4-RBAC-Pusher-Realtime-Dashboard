class AddressHelper {
    constructor(config) {
        this.el = {
            prov: document.querySelector('[data-address-level="provinsi"]'),
            kab:  document.querySelector('[data-address-level="kabupaten"]'),
            kec:  document.querySelector('[data-address-level="kecamatan"]'),
            kel:  document.querySelector('[data-address-level="kelurahan"]')
        };
        this.initValues = config.initValues || {};
        this.baseUrl = '/api/wilayah';
        this.run();
    }

    async run() {
        if (this.el.prov) {
            await this.loadData(`${this.baseUrl}/provinsi`, this.el.prov, "Provinsi", this.initValues.prov);
            
            this.el.prov.onchange = () => this.handleChain(this.el.prov, this.el.kab, 'kota', "Kabupaten");
            this.el.kab.onchange  = () => this.handleChain(this.el.kab,  this.el.kec, 'kecamatan', "Kecamatan");
            this.el.kec.onchange  = () => this.handleChain(this.el.kec,  this.el.kel, 'desa', "Kelurahan");
        }
    }

    async handleChain(parentEl, childEl, type, label) {
        if (!childEl) return;
        const selectedOpt = parentEl.options[parentEl.selectedIndex];
        const code = selectedOpt.getAttribute('data-code'); // Ambil Kode Wilayah untuk API
        
        if (code) {
            await this.loadData(`${this.baseUrl}/${type}/${code}`, childEl, label);
        }
    }

    async loadData(url, targetEl, label, initialValue) {
        targetEl.innerHTML = '<option value="">Loading...</option>';
        try {
            const res = await fetch(url);
            const result = await res.json();
            targetEl.innerHTML = `<option value="">-- Pilih ${label} --</option>`;
            
            result.data.forEach(item => {
                // VALUE yang disimpan ke DB adalah NAMA (sesuai gambar generator Anda)
                // KODE disimpan di atribut data-code untuk kebutuhan API level bawahnya
                const isSelected = (item.name == initialValue) ? 'selected' : '';
                targetEl.innerHTML += `<option value="${item.name}" data-code="${item.code}" ${isSelected}>${item.name}</option>`;
            });
            
            if (initialValue) targetEl.dispatchEvent(new Event('change'));
        } catch (e) {
            targetEl.innerHTML = `<option value="">Gagal Memuat</option>`;
        }
    }
}