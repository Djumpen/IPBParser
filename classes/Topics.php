<?php

namespace IPBParser\Classes;

class Topics {

    private $registry;

    private $forum;

    private $page_dom;

    private $cur_topics_index;

    private $pages_finished = 0;

    public function __construct($registry){
        $this->registry = $registry;
    }

    public function parseOne($forum_id = null){
        if($forum_id){
            $this->forum = $this->registry->db->select('forum', 'forum_id = ' . $forum_id)->fetchObject();
        } else {
            $sql = 'SELECT * FROM forum WHERE parsed_pages IS NULL OR parsed_pages < pages ORDER BY forum_id';
            $this->forum = $this->registry->db->query($sql)->fetchObject();
        }

        if(!$this->forum){
            die("No forums for parse\n");
        } else {
            $this->_parse();
        }
    }

    private function _parse(){

        if (!$this->forum->link) {
            $this->registry->log->error('No link in forum ' . $this->forum->forum_id);
            die('Check DB - forum_id ' . $this->forum->forum_id . " has no link\n");
        }

        // Define current page
        $this->cur_topics_index = $this->registry->config->topics_per_page * (int)$this->forum->parsed_pages;

        $this->_parsePage();

        // Check if need to parse next page
        if($this->forum->parsed_pages < $this->forum->pages){
            $fppr = $this->registry->config->forum_pages_per_request;
            if(!$fppr || $this->pages_finished < $fppr){
                $this->_parse();
            }
        } else {
            echo "Parsing finished. " . $this->registry->request->getCommonTime();
        }
    }

    private function _parsePage(){
        $link_component = 'page__prune_day__100__sort_by__Z-A__sort_key__last_post__topicfilter__all__st__' . $this->cur_topics_index;
        $this->page_dom = $this->registry->request->getDom($this->forum->link . $link_component);

        foreach ($this->page_dom->find('.rowContent') as $pageRow) {
            $pages = 1;
            if ($pagi_dom = $pageRow->find('.mini_pagination', 0)) {
                $num_pages_str = $pagi_dom->plaintext;
                $tmp = explode(': ', $num_pages_str);
                if (isset($tmp[1]) && $tmp[1])
                    $pages = $tmp[1];
            }

            $tp = $pageRow->find('.topic_prefix', 0);
            $topic = [
                'forum_id' => $this->forum->forum_id,
                'title' => $pageRow->find('a', 0)->plaintext,
                'link' => $pageRow->find('a', 0)->href,
                'is_fixed' => ($tp && trim($tp->plaintext) == 'Fixed') ? 1 : 0,
                'pages' => $pages
            ];

            $this->registry->db->insert('topic', $topic);
        }

        $this->_updateForumStats([
            'parsed_pages' => $this->forum->parsed_pages + 1
        ]);

        $this->pages_finished++;

        echo 'Forum #' . $this->forum->forum_id . ': parsed ' . $this->forum->parsed_pages . '/' . $this->forum->pages . " pages\n";
    }

    private function _updateForumStats($data){
        $this->registry->db->update('forum', $data, "forum_id = {$this->forum->forum_id}");
        isset($data['parsed_pages']) ? $this->forum->parsed_pages = $data['parsed_pages'] : '';
    }


}