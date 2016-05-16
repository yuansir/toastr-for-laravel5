<?php

namespace Yuansir\Toastr;

use Illuminate\Config\Repository;
use Illuminate\Session\SessionManager;

class Toastr
{
    /**
     * @var SessionManager
     */
    protected $session;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var array
     */
    protected $notifications = [];

    /**
     * Toastr constructor.
     *
     * @param SessionManager $session
     * @param Repository     $config
     */
    public function __construct(SessionManager $session, Repository $config)
    {
        $this->session = $session;
        $this->config = $config;
    }

    public function render()
    {
        $notifications = $this->session->get('toastr:notifications');

        if (!$notifications) {
            return '';
        }

        foreach ($notifications as $notification) {
            $config = $this->config->get('toastr.options');
            $javascript = '';
            $options = [];
            if ($config) {
                $options = array_merge($config, $notification['options']);
            }

            if ($options) {
                $javascript = 'toastr.options = '.json_encode($options).';';
            }

            $message = str_replace("'", "\\'", $notification['message']);
            $title = $notification['title'] ? str_replace("'", "\\'", $notification['title']) : null;
            $javascript .= " toastr.{$notification['type']}('$message','$title');";
        }

        return view('Toastr::toastr', compact('javascript'));
    }

    /**
     * Add notification.
     *
     * @param $type
     * @param $message
     * @param null  $title
     * @param array $options
     *
     * @return bool
     */
    public function add($type, $message, $title = null, $options = [])
    {
        $types = ['info', 'warning', 'success', 'error'];
        if (!in_array($type, $types)) {
            return false;
        }

        $this->notifications[] = [
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'options' => $options,
        ];
        $this->session->flash('toastr:notifications', $this->notifications);
    }

    /**
     * Add info notification.
     *
     * @param $message
     * @param null  $title
     * @param array $options
     */
    public function info($message, $title = null, $options = [])
    {
        $this->add('info', $message, $title, $options);
    }

    /**
     * Add warning notification.
     *
     * @param $message
     * @param null  $title
     * @param array $options
     */
    public function warning($message, $title = null, $options = [])
    {
        $this->add('warning', $message, $title, $options);
    }

    /**
     * Add success notification.
     *
     * @param $message
     * @param null  $title
     * @param array $options
     */
    public function success($message, $title = null, $options = [])
    {
        $this->add('success', $message, $title, $options);
    }

    /**
     * Add error notification.
     *
     * @param $message
     * @param null  $title
     * @param array $options
     */
    public function error($message, $title = null, $options = [])
    {
        $this->add('error', $message, $title, $options);
    }

    /**
     * Clear notifications.
     */
    public function clear()
    {
        $this->notifications = [];
    }
}
