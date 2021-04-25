<?php

namespace Model;

use CI_Emerald_Model;
use App;
use Exception;
use stdClass;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Comment_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'comment';


    /** @var int */
    protected $user_id;
    /** @var int */
    protected $assign_id;
    /** @var int */
    protected $parent_comment_id;
    /** @var string */
    protected $text;

    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    // generated
    protected $comments;
    protected $likes;
    protected $user;


    /**
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function set_user_id(int $user_id)
    {
        $this->user_id = $user_id;
        return $this->save('user_id', $user_id);
    }

    /**
     * @return int
     */
    public function get_assign_id(): int
    {
        return $this->assign_id;
    }

    /**
     * @param int $assign_id
     *
     * @return bool
     */
    public function set_assign_id(int $assign_id)
    {
        $this->assign_id = $assign_id;
        return $this->save('assign_id', $assign_id);
    }


    /**
     * @return string
     */
    public function get_text(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    public function set_text(string $text)
    {
        $this->text = $text;
        return $this->save('text', $text);
    }


    /**
     * @return string
     */
    public function get_time_created(): string
    {
        return $this->time_created;
    }

    /**
     * @param string $time_created
     *
     * @return bool
     */
    public function set_time_created(string $time_created)
    {
        $this->time_created = $time_created;
        return $this->save('time_created', $time_created);
    }

    /**
     * @return string
     */
    public function get_time_updated(): string
    {
        return $this->time_updated;
    }

    /**
     * @param string $time_updated
     *
     * @return bool
     */
    public function set_time_updated(int $time_updated)
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    // generated

    /**
     * @return mixed
     */
    public function get_likes()
    {
        return $this->likes;
    }

    /**
     * @return mixed
     */
    public function get_comments()
    {
        return $this->comments;
    }

    /**
     * @return User_model
     */
    public function get_user(): User_model
    {
        if (empty($this->user)) {
            try {
                $this->user = new User_model($this->get_user_id());
            } catch (Exception $exception) {
                $this->user = new User_model();
            }
        }
        return $this->user;
    }

    function __construct($id = NULL)
    {
        parent::__construct();
        $this->set_id($id);
    }

    public function reload(bool $for_update = FALSE)
    {
        parent::reload($for_update);

        return $this;
    }

    public static function create(array $data)
    {
        App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();
        return new static(App::get_ci()->s->get_insert_id());
    }

    public function delete()
    {
        $this->is_loaded(TRUE);
        App::get_ci()->s->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();
        return (App::get_ci()->s->get_affected_rows() > 0);
    }

    /**
     * @param int $assign_id
     * @return self[]
     * @throws Exception
     */
    public static function get_all_by_assign_id(int $assign_id)
    {

        $data = App::get_ci()->s->from(self::CLASS_TABLE)->where(['assign_id' => $assign_id])->orderBy('time_created')->many();
        $ret = [];
        foreach ($data as $i) {
            $ret[] = (new self())->set($i);
        }
        return $ret;
    }

    /**
     * @throws Exception
     */
    public static function get_tree(int $assign_id)
    {
        $flat =  App::get_ci()->s->from(self::CLASS_TABLE)->where(['assign_id' => $assign_id])->orderBy('time_created')->many();
        $tree =  self::makeRecursive($flat, 0, 'parent_comment_id', 'id', 'comments');

        return array_map(function ($item) {
            return (new self())->set($item);
        }, $tree);
    }

    /**
     * преобразует массив в рекурсивный массив дерева с детьми
     *
     * @param array   $d   flat data, implementing a id/parent id (adjacency list) structure
     * @param mixed   $r   root id, node to return
     * @param string  $pk  parent id index
     * @param string  $k   id index
     * @param string  $c   children index
     * @return array
     */
    public static function makeRecursive(array $d, $r = 0, $pk = 'parent', $k = 'id', $c = 'children'): array
    {
        $m = [];
        foreach ($d as $e) {
            isset($m[$e[$pk]]) ?: $m[$e[$pk]] = [];
            isset($m[$e[$k]]) ?: $m[$e[$k]] = [];
            $m[$e[$pk]][] = array_merge($e, [$c => &$m[$e[$k]]]);
        }

        return $m[$r]; // remove [0] if there could be more than one root nodes
    }

    /**
     * @param self|self[] $data
     * @param string $preparation
     * @return stdClass|stdClass[]
     * @throws Exception
     */
    public static function preparation($data, $preparation = 'default')
    {
        switch ($preparation) {
            case 'full_info':
                return self::_preparation_full_info($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }


    /**
     * @param self[] $data
     * @return stdClass[]
     */
    private static function _preparation_full_info($data)
    {
        $ret = [];

        foreach ($data as $d) {
            $o = new stdClass();

            $o->id = $d->get_id();
            $o->text = $d->get_text();

            $o->user = User_model::preparation($d->get_user(), 'main_page');
            $o->comments = self::preparation(array_map(function ($item) {
                return (new self())->set($item);
            }, $d->get_comments()), 'full_info');

            $o->likes = rand(0, 25);

            $o->time_created = $d->get_time_created();
            $o->time_updated = $d->get_time_updated();

            $ret[] = $o;
        }


        return $ret;
    }


}
