<?php
/*
* This file is part of rg\broker.
*
* (c) ResearchGate GmbH <bastian.hofmann@researchgate.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
?>
<div class="row-fluent">
    <ul class="nav nav-pills pull-right">
        <li class="active">
            <a href="javascript:;">
                <?php echo $currentRepository->getName(); ?>
            </a>
        </li>
        <li>
            <a href="<?php echo ROOTURL . '/repositories/' . $currentRepository->getName() . '/packages.json'; ?>">
                package.json
            </a>
        </li>
    </ul>
</div>
<div class="row-fluent">
<div class="accordion" id="accordion1">
        <div class="accordion-group">
            <div class="accordion-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#collapseOne">
                    composer.json Repository reference
                </a>
            </div>
            <div id="collapseOne" class="accordion-body collapse" style="height: 0px; ">
                <div class="accordion-inner">
<pre class="prettyprint linenums lang-js">
"repositories":{
    "packagist": false,
    "rg-broker": {
        "composer": {
        "url": "<?php echo ROOTURL . '/repositories/' . $currentRepository->getName() ?>"
    }
    }
}
</pre
                </div>
            </div>
        </div>
    </div>
</div>