<?php

namespace IPBParser\Classes;

class Forums {

    private $registry;

    public function __construct($registry){
        $this->registry = $registry;
    }

    public function parse(){
        $dom = $this->registry->request->getDom($this->registry->config->target_url);

        $links = [];
        foreach($dom->find('.rowContent') as $row){
            $links[] = [
                'title' => $row->find('a', 0)->plaintext,
                'link' => $row->find('a', 0)->href,
                'pages' => ceil((int)$row->find('.number', 0)->plaintext / $this->registry->config->topics_per_page),
                'topics' => (int)$row->find('.number', 0)->plaintext
            ];
        }

        foreach ($links as $link) {
            $this->registry->db->insert('forum', $link);
        }

        $count = count($links);
        $this->registry->log->info('Added ' . $count . ' forums');
        echo "Parsing finished. " . $this->registry->request->getCommonTime();
    }
}