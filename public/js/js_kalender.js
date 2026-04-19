document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar-academic');

    if (calendarEl) {
        // Ambil URL API dari data-attribute HTML
        const eventUrl = calendarEl.dataset.url;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            // Tampilan awal (bisa diganti ke 'multiMonthYear' jika ingin default setahun)
            initialView: 'dayGridMonth', 
            themeSystem: 'bootstrap5',
            locale: 'id',
            
            // Pengaturan Tombol di Atas
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'multiMonthYear,dayGridMonth,listMonth'
            },

            // Penamaan Tombol agar Bahasa Indonesia
            buttonText: {
                today: 'Hari Ini',
                multiMonthYear: 'Tahun',
                month: 'Bulan',
                list: 'Agenda'
            },

            // Konfigurasi khusus untuk tampilan tahunan
            views: {
                multiMonthYear: {
                    multiMonthMaxColumns: 3 // Menampilkan 3 bulan per baris
                }
            },

            // Sumber Data dari Controller
            events: eventUrl,
            
            // Pengaturan Tinggi (auto agar tidak scroll saat tampilan tahun)
            height: 'auto',

            // LOGIKA PEWARNAAN OTOMATIS (Fix Warna Biru)
            eventDidMount: function(info) {
                const hexColor = info.event.extendedProps.hex_color;
                const className = info.event.extendedProps.class_name;

                if (hexColor) {
                    // Jika class mengandung '-lt' (Light/Soft) ala Tabler
                    if (className && className.includes('-lt')) {
                        // Background transparan (HEX + 20 untuk opacity 12%)
                        info.el.style.backgroundColor = hexColor + '20'; 
                        info.el.style.color = hexColor; 
                        info.el.style.borderLeft = '4px solid ' + hexColor;
                        info.el.style.borderTop = 'none';
                        info.el.style.borderRight = 'none';
                        info.el.style.borderBottom = 'none';
                        info.el.style.paddingLeft = '5px';
                    } else {
                        // Jika warna solid
                        info.el.style.backgroundColor = hexColor;
                        info.el.style.borderColor = hexColor;
                        info.el.style.color = '#ffffff';
                    }

                    // Memaksa warna teks pada judul agar terbaca
                    const titleEl = info.el.querySelector('.fc-event-title');
                    if (titleEl) {
                        titleEl.style.fontWeight = '600';
                        if (className && className.includes('-lt')) {
                            titleEl.style.color = hexColor;
                        } else {
                            titleEl.style.color = '#ffffff';
                        }
                    }
                }
            },

            // Efek loading saat ambil data
            loading: function(isLoading) {
                if (isLoading) {
                    calendarEl.style.opacity = '0.5';
                } else {
                    calendarEl.style.opacity = '1';
                }
            }
        });

        calendar.render();
    }
});