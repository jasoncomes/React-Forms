<?php 
if (!get_field('cta_include')) {
    return;
} 
?>

<div class="webform-cta">

    <div class="row cta-copy">
        <div class="col-sm-12">
            <h2><?php the_field('cta_title'); ?></h2>
            <?php if ($cta_description = get_field('cta_description')) : ?>
            <div class="cta-content <?php if ($icon = get_field('cta_icon')) { echo 'cta-content-image'; } ?>">
                <?php if (!empty($icon)) { echo '<img class="cta-icon" src="' . $icon . '" />'; } ?>
                <?php echo $cta_description; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (get_field('cta_include_memberships')) : ?>
        <div class="row cta-memberships">
            <div class="col-md-4">
                <div class="cta-membership">
                    <h3>Employer Partner</h3>
                    <p class="period">1-Year Membership</p>
                    <p class="description">Select if your employer is partnered with Profile. Employers not listed can <a rel="noopener noreferrer" href="https://profileplan.net/businesses/">register</a> to become one.</p>
                    <p class="price"><sup>$</sup>150</p>
                    <p class="savings"><strong>$2.88 per week*</strong> SAVE 50%</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="cta-membership">
                    <h3>Physician Referral</h3>
                    <p class="period">1-Year Membership</p>
                    <p class="description">Check with your physician or healthcare provider for a referral and save 50% on your membership.</p>
                    <p class="price"><sup>$</sup>150</p>
                    <p class="savings"><strong>$2.88 per week*</strong> SAVE 50%</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="cta-membership">
                    <h3>Standard</h3>
                    <p class="period">1-Year Membership</p>
                    <p class="description">Join the thousands of members who have lost weight and kept it off.</p>
                    <p class="price"><sup>$</sup>300</p>
                    <p class="savings"><strong>$5.77 per week*</strong></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (get_field('cta_include_buttons')) : ?>
        <div class="row cta-buttons">
            <div class="col-md-3 col-md-offset-1">
                <h3>I'm Ready!</h3>
                <a class="btn-white" href="/join-now">Join Now</a>
            </div>
            <div class="col-md-8">
                <h3>Tell Me More</h3>
                <a class="btn-white" href="/meet-with-coach">Meet with Coach</a>
                <a class="btn-white" href="/discovery-session">Discovery Session</a>
            </div>
        </div>
    <?php endif; ?>

</div>