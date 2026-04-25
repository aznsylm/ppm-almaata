<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->helper(array('form', 'security'));
        $this->load->library('form_validation');
    }

    public function login()
    {
        if ($this->current_user()) {
            $this->redirect_after_login($this->current_user()['role']);
            return;
        }

        if ($this->input->method(TRUE) === 'POST') {
            $this->form_validation->set_rules('nim', 'NIM', 'required|trim|min_length[5]|max_length[15]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|max_length[100]');

            if ($this->form_validation->run()) {
                $nim = trim($this->input->post('nim', TRUE));
                $password = (string) $this->input->post('password', TRUE);

                $adminLogin = $this->attempt_admin_login($nim, $password);
                if ($adminLogin['ok']) {
                    $this->set_login_session($adminLogin['session']);
                    $this->redirect_after_login('admin');
                    return;
                }

                $userLogin = $this->attempt_user_login($nim, $password);
                if ($userLogin['ok']) {
                    $this->set_login_session($userLogin['session']);
                    if (!empty($userLogin['must_reset_password'])) {
                        redirect('auth/reset-password');
                        return;
                    }
                    $this->redirect_after_login('user');
                    return;
                }

                $this->session->set_flashdata('error', 'NIM atau password tidak valid.');
            }
        }

        $data = array(
            'page_title' => 'Login PPM Alma Ata',
            'content_view' => 'auth/login_content'
        );

        $this->load->view('layouts/auth', $data);
    }

    public function reset_password()
    {
        $this->require_role(array('user'));
        $auth = $this->current_user();

        if (empty($auth['must_reset_password'])) {
            redirect('user/dashboard');
            return;
        }

        if ($this->input->method(TRUE) === 'POST') {
            $this->form_validation->set_rules('new_password', 'Password Baru', 'required|min_length[8]|max_length[100]');
            $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|matches[new_password]');

            if ($this->form_validation->run()) {
                $newPassword = (string) $this->input->post('new_password', TRUE);
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $this->User_model->update_password($auth['user_id'], $hash);

                $auth['must_reset_password'] = 0;
                $this->set_login_session($auth);
                $this->session->set_flashdata('success', 'Password berhasil diperbarui.');
                redirect('user/dashboard');
                return;
            }
        }

        $data = array(
            'page_title' => 'Reset Password',
            'content_view' => 'auth/reset_password_content'
        );

        $this->load->view('layouts/auth', $data);
    }

    public function logout()
    {
        $this->destroy_login_session();
        $this->session->sess_destroy();
        redirect('auth/login');
    }

    private function attempt_admin_login($nim, $password)
    {
        $adminNim = trim((string) $this->config->item('ppm_admin_nim'));
        $adminHash = (string) $this->config->item('ppm_admin_password_hash');

        if ($adminNim === '' || $adminHash === '') {
            return array('ok' => FALSE);
        }

        if ($nim !== $adminNim) {
            return array('ok' => FALSE);
        }

        if (!password_verify($password, $adminHash)) {
            return array('ok' => FALSE);
        }

        return array(
            'ok' => TRUE,
            'session' => array(
                'user_id' => 0,
                'nim' => $adminNim,
                'role' => 'admin',
                'display_name' => 'Admin PPM',
                'must_reset_password' => 0,
                'is_admin_env' => 1,
                'last_seen' => date('Y-m-d H:i:s')
            )
        );
    }

    private function attempt_user_login($nim, $password)
    {
        $user = $this->User_model->find_by_nim($nim);
        if (!$user || $user['role'] !== 'user' || $user['status'] !== 'active') {
            return array('ok' => FALSE);
        }

        $valid = FALSE;
        if (!empty($user['password_hash'])) {
            $valid = password_verify($password, $user['password_hash']);
        } elseif (!empty($user['legacy_password_md5'])) {
            $valid = hash_equals(strtolower($user['legacy_password_md5']), md5($password));
        }

        if (!$valid) {
            return array('ok' => FALSE);
        }

        $this->User_model->touch_last_login($user['id']);

        $msmhs = $this->User_model->find_msmhs_by_nim($user['nim']);
        $displayName = !empty($msmhs['nama']) ? $msmhs['nama'] : $user['nim'];

        $mustReset = (int) $user['must_reset_password'];
        return array(
            'ok' => TRUE,
            'must_reset_password' => $mustReset,
            'session' => array(
                'user_id' => (int) $user['id'],
                'nim' => $user['nim'],
                'role' => 'user',
                'display_name' => $displayName,
                'must_reset_password' => $mustReset,
                'is_admin_env' => 0,
                'last_seen' => date('Y-m-d H:i:s')
            )
        );
    }

    private function redirect_after_login($role)
    {
        if ($role === 'admin') {
            redirect('admin/dashboard');
            return;
        }

        redirect('user/dashboard');
    }
}
