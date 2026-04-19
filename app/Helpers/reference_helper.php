<?php

if (!function_exists('get_ref')) {
    /**
     * Mengambil RfName berdasarkan RfGroup dan Rfid
     */
    function get_ref($group, $id) {
        $db = \Config\Database::connect();
        
        $row = $db->table('auth_referensi') // Sesuaikan nama tabel Anda
                  ->select('RfName')
                  ->where('RfGroup', $group)
                  ->where('Rfid', $id)
                  ->where('is_deleted', 'N')
                  ->get()
                  ->getRow();

        return $row ? $row->RfName : '-';
    }
}

if (!function_exists('get_ref_list')) {
    /**
     * Mengambil daftar referensi berdasarkan group (untuk Dropdown/Select)
     */
    function get_ref_list($group) {
        $db = \Config\Database::connect();
        
        return $db->table('auth_referensi')
                  ->where('RfGroup', $group)
                  ->where('is_deleted', 'N')
                  ->orderBy('RfName', 'ASC')
                  ->get()
                  ->getResultArray();
    }
}
if (!function_exists('getOption')) {
    function getOption($group, $selected = null)
    {
        $db = \Config\Database::connect();

        $rows = $db->table('auth_referensi')
            ->where('RfGroup', $group)
            ->where('is_deleted', 'N')
            ->orderBy('RfName', 'ASC')
            ->get()
            ->getResult();

        $html = '';

        foreach ($rows as $r) {
            $sel = ($selected == $r->Rfid) ? 'selected' : '';
            $html .= "<option value=\"{$r->Rfid}\" {$sel}>{$r->RfName}</option>";
        }

        return $html;
    }
}

if (!function_exists('getOptionDb')) {
    function getOptionDb($table, $selected = null, $valueField = 'id', $labelField = null)
    {
        $db = \Config\Database::connect();

        // Ambil nama kolom
        $columns = $db->getFieldNames($table);

        // Tentukan label otomatis
        if (!$labelField) {
            if (in_array('nama', $columns)) {
                $labelField = 'nama';
            } elseif (in_array('keterangan', $columns)) {
                $labelField = 'keterangan';
            } else {
                $labelField = $columns[1] ?? $valueField;
            }
        }

        $rows = $db->table($table)->get()->getResult();

        $html = '';
        foreach ($rows as $r) {
            $val = $r->$valueField;
            $label = $r->$labelField;
            $sel = ($selected == $val) ? 'selected' : '';

            $html .= "<option value=\"{$val}\" {$sel}>{$label}</option>";
        }

        return $html;
    }


    if (!function_exists('format_uang')) {
        function format_uang($nominal) {
            return "Rp " . number_format($nominal, 0, ',', '.');
        }
    }

    if (!function_exists('tgl_short')) {
        function tgl_short($date) {
            if (!$date || $date == '0000-00-00') return "-";
            return date('d/m/Y', strtotime($date));
        }
    }

    if (!function_exists('tgl_long')) {
        function tgl_long($date) {
            if (!$date) return "-";
            $bulan = [
                1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            $split = explode('-', date('Y-m-d', strtotime($date)));
            return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
        }
    }
}