<?php

namespace Modules\Auth\Controllers;

use App\Controllers\BaseController;
use Modules\Auth\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        return view('Modules\Auth\Views\login');
    }

    public function login_action()
    {
        $model = new UserModel();

        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        $user = $model->where('userName', $username)->first();
        if ($user) {
            if (password_verify($password, $user['userPassword'])) {
                session()->set([
                    'userID'    => $user['id'],
                    'userName'  => $user['userName'],
                    'realName'  => $user['realName'],
                    'role_id'    => $user['role_id'],
                    'logged_in' => true,
                ]);
                $model->save_auth_log($username, $user['id'], 1);
                helper('discovery');
                $features = get_user_features($user['userName']);
                session()->set('user_features', $features);
                return redirect()->to('/dashboard'); 
            } else {
                $model->save_auth_log($username, $user['id'], 0);
                return redirect()->back()->with('error', 'Password salah.');
            }
        } else {
            $model->save_auth_log($username, null, 0);
            return redirect()->back()->with('error', 'Username tidak ditemukan.');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    public function my_account() {
        return view('Modules\Auth\Views\profile', [
            'title' => 'Pengaturan Akun',
            // Ambil data user dari DB jika perlu data tambahan di luar session
        ]);
    }

    public function update_account() {
        $model = new UserModel();
        $id = session()->get('id_user'); // Pastikan ID tersedia di session
        $data = [
            'userName' => $this->request->getPost('nama_user'),
            'email'     => $this->request->getPost('email'),
        ];

        if ($model->update($id, $data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Profil diperbarui']);
        }
    }

}