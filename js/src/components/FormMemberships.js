import React, { Component } from 'react';

import memberships from '../data/memberships';


class FormMemberships extends Component {


    // Construct.
    constructor() {
        super();

        // Bind methods.
        this.saveAndNext      = this.saveAndNext.bind(this);
        this.validatePromo    = this.validatePromo.bind(this);
        this.renderMembership = this.renderMembership.bind(this);
    }


    // Validate Promo Code
    validatePromo(ev) {

        // Prevent Default Submit.
        ev.preventDefault();

        // Promo variable.
        const promoBox = document.getElementById('promoMessage');
        let promoValidation = false;

        // Remove classes.
        promoBox.classList.remove('is-invalid')
        promoBox.classList.remove('is-valid')

        // If no value, don't do a thing.
        if (!this.promo.value) {
            return;
        }

        // Discount ~ Promo Price.
        this.props.state.discounts.promos.map((promo) => {
            if (promo.code.toLowerCase() !== this.promo.value.toLowerCase()) {
                return false
            }   

            // Update PromoBox.
            promoValidation = true
            promoBox.setAttribute('data-promo', promo.title)
            promoBox.setAttribute('data-price', promo.price)
            return false
        })

        // Error classes.
        if (promoValidation) {
            promoBox.classList.add('is-valid')
        } else {
            promoBox.classList.add('is-invalid')
        }
    }


    // Save & Next Button Event.
    saveAndNext(ev, membership) {

        // Prevent Default Submit.
        ev.preventDefault();

        // Employer Value
        const employer = this.employer && this.employer.value ? this.employer.value : '';

        // Promo Value
        const promo = membership === 'MF-1001' && this.promo && this.promo.value ? this.promo.value : '';

        // Update User State.
        this.props.saveState({
            user: {
                ...this.props.state.user,
                employer,
                promo,
                membership
            }
        })

        // AJAX Wordpress DB
        this.props.ajaxWordpressDB({
            employer,
            promo,
            membership
        })

        // Next Step.
        this.props.nextStep()
    }


    // Render Memberships.
    renderMembership(key) {

        // Employer Membership - Employee Options
        const employers = this.props.state.employers;
        let employerSelectOptions;

        if (key === 'MF-1000') {
            employerSelectOptions = <select ref={(input) => this.employer = input} defaultValue={this.props.state.user.employer} required={true}>
                                        <option value="">Select Your Employer</option>
                                        {employers.map(key => <option key={key} value={key}>{key}</option>)}
                                    </select>
        }

        // Standard Form - Promo Code
        let promoCodeInput;
        
        if (key === 'MF-1001') {
            promoCodeInput = <div className="promo">
                                <div className="promoInput">
                                    <input ref={(input) => this.promo = input} name="promo" type="text" autoComplete="off" placeholder="Enter Promo Code" />
                                    <button onClick={(e) => this.validatePromo(e)}>Validate</button>
                                </div>
                                <div id="promoMessage"></div>
                            </div>
        }

        return (
            <div className="col-md-4" key={key} value={key}>
                <form className="box" onSubmit={(e) => this.saveAndNext(e, key)}>
                    <h3>{memberships[key].name}</h3>
                    <p className="period">{memberships[key].period}</p>
                    <div className="description" dangerouslySetInnerHTML={memberships[key].descriptionHTML} />
                    <p className="price"><sup>$</sup>{memberships[key].price}</p>
                    <div className="savings" dangerouslySetInnerHTML={memberships[key].savingsHTML} />
                    <hr />
                    {employerSelectOptions}
                    {promoCodeInput}
                    <input className="btn-submit" type="submit" value="Select &amp; Continue" />
                    <p className="legal">{memberships[key].legal}</p>
                </form>
            </div>
        )
    }


    // Render.
    render() {
        return (
            <div className="memberships row">
                {Object.keys(memberships).map(this.renderMembership)}
                <div className="col-md-12">
                    <p className="note">*State and local taxes may apply. Prepayment of a full year membership is required. Average weekly cost is based on 52 weeks.</p>
                </div>
            </div>
        );
    }
}

export default FormMemberships;
