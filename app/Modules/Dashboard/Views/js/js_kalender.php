document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar-academic');
    const eventUrl = calendarEl.dataset.url;

    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            // Tampilan awal tetap bulanan agar tidak terlalu berat saat load pertama
            initialView: 'dayGridMonth', 
            themeSystem: 'bootstrap5',
            locale: 'id',
            
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                // Tambahkan multiMonthYear di sini
                right: 'multiMonthYear,dayGridMonth,listMonth' 
            },

            // Memberikan nama pada tombol agar muncul teks "Tahun"
            buttonText: {
                today: 'Hari Ini',
                multiMonthYear: 'Tahun', // Ini tombol untuk tampilan 12 bulan
                month: 'Bulan',
                list: 'Agenda'
            },

            // Konfigurasi khusus tampilan tahunan
            views: {
                multiMonthYear: {
                    type: 'multiMonthYear',
                    duration: { years: 1 },
                    multiMonthMaxColumns: 3 // Menampilkan 3 bulan per baris
                }
            },

            events: eventUrl,
            // Jika tampilan setahun, tingginya sebaiknya auto agar tidak terpotong
            height: 'auto', 
            
            eventDidMount: function(info) {
                if (info.event.extendedProps.className) {
                    info.el.classList.add(info.event.extendedProps.className);
                }
            }
        });

        calendar.render();
    }
});