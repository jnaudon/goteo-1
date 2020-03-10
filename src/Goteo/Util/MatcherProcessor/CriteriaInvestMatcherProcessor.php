<?php
/*
 * This file is part of the Goteo Package.
 *
 * (c) Platoniq y Fundación Goteo <fundacion@goteo.org>
 *
 * For the full copyright and license information, please view the README.md
 * and LICENSE files that was distributed with this source code.
 */

namespace Goteo\Util\MatcherProcessor;

use Goteo\Util\MatcherProcessor\AbstractMatcherProcessor;
use Goteo\Util\MatcherProcessor\MatcherProcessorException;
use Goteo\Model\Matcher;
use Goteo\Library\Text;
use Goteo\Model\Invest;

/**
 * This Processor duplicates invests with some (customizable) limits
 */
class CriteriaInvestMatcherProcessor extends AbstractMatcherProcessor {
    protected $default_vars = [
        'percent_of_donation' => 50,
        'min_number_of_donors' => 0,
        'donation_per_project' => 2000
    ];

    /**
     * Checks if this invests has to be matched and
     * returns the amount to be added
     */
    public function getAmount(&$error = '') {
        $invest = $this->getInvest();
        $project = $this->getProject();
        $matcher = $this->getMatcher();
        $vars = $this->getVars();
        $amount = $invest->amount;
        $invested = Invest::getList(['methods' => 'pool', 'status' => Invest::$ACTIVE_STATUSES, 'projects' => $project,'users' => $matcher->getUsers()], null, 0, 0, 'money');
        $has_reached_percent = ($project->getAmountPercent() >= $vars['percent_of_donation']);
        $has_reached_donors = ($vars['min_number_of_donors']) ? ($vars['min_number_of_donors'] < $project->num_investors) : true;
        if ($has_reached_percent && $has_reached_donors && !$invested) {
            $amount = $vars['donation_per_project'];
        } else {
            $amount = 0;
        }
        
        // Check if there's enough total to extract from user's pool
        if($matcher->getAvailableAmount() < $amount) {
            $error = 'Matcher funds exhausted';
            $amount = $matcher->getAvailableAmount();
        }
        
        return $amount;

    }

}
