<?php

namespace App\View\Helper;

use App\Model\Entity\Vote;
use Cake\Core\Configure;
use Cake\View\Helper;
use Cake\View\View;

/**
 * Votes helper
 */
class VotesHelper extends Helper
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * @param bool $bool
     *
     * @return string
     */
    public function format(bool $bool): string
    {
        return mb_strtolower($bool ? Vote::YES : Vote::NO, 'utf-8');
    }

}
