import React, { Component } from 'react';
import Cleave from 'cleave.js/react';

import forms from '../data/forms';


class FormAccount extends Component {

    // Construct.
    constructor() {
        super();

        // Bind methods.
        this.saveAndNext = this.saveAndNext.bind(this);
        this.handleLocationChange = this.handleLocationChange.bind(this);
    }


    // Steps Transition.
    componentWillUnmount() {
        const discounts = this.props.state.discounts;

        switch (forms[this.props.state.form].type) {

            // Form ~ Payment Type.
            case 'payment':

                // Skip Memberships Step ~ Discounts.
                if (
                    discounts.limited || // Discount ~ Limited Time Skip.
                    (this.location.value && discounts.locations && discounts.locations.hasOwnProperty(this.location.value)) // Discount ~ Location Skip.
                ) {
                    this.props.saveState({step: 3})
                }
                break;

            // Form ~ Singup Type.
            case 'signup':

                // Skip Membership & Payment Steps ~ Signup Forms.
                this.props.saveState({step: 4})
                break;

            default:
                break;
        }
   }


    handleLocationChange(ev) {

        // Prevent Default Submit.
        ev.preventDefault()

        // Only Update Discovery Session.
        if (this.props.state.form !== 'discovery-session') {
            return;
        }

        // Location Change, Updates Events.
        this.props.saveState({
            user: {
                ...this.props.state.user,
                location: this.location.value
            }
        })
    }


    // Save & Next Button Event.
    saveAndNext(ev) {

        // Prevent Default Submit.
        ev.preventDefault()

        // Confirm Email
        if (this.email.value !== this.confirmEail.value) {
            this.email.classList.add('input-error');
            this.confirmEail.classList.add('input-error');
            return;
        }

        // Save User State.
        this.props.saveState({
            user: {
                ...this.props.state.user,
                firstName: this.firstName.value,
                lastName: this.lastName.value,
                email: this.email.value,
                phone: this.phone.element.value,
                location: this.location.value,
                event: this.event && this.event.value ? this.event.value : ''
            }
        })

        // AJAX Wordpress DB
        this.props.ajaxWordpressDB({
            first_name: this.firstName.value,
            last_name: this.lastName.value,
            email: this.email.value,
            phone: this.phone.element.value,
            location: this.props.state.locations[this.location.value].id,
            event: this.event && this.event.value ? this.event.value : '',
            form: this.props.state.form,
            membership: this.props.state.user.membership ? this.props.state.user.membership : ''
        })

        // AJAX Email
        this.props.ajaxEmail({
            first_name: this.firstName.value,
            last_name: this.lastName.value,
            email: this.email.value,
            phone: this.phone.element.value,
            location: this.props.state.locations[this.location.value].title,
            event: this.event && this.event.value ? this.event.value : '',
        })

        // Next Step.
        this.props.nextStep()
    }


    // Render
    render() {
        const locations = this.props.state.locations;

        // Event Select
        let eventSelectOptions;
        if (this.props.state.form === 'discovery-session' && this.props.state.user.location && locations[this.props.state.user.location].events.length) {
            eventSelectOptions = <select ref={(input) => this.event = input} defaultValue={this.props.state.user.event} required={true}>
                                    <option value="">Select An Event</option>
                                    {this.props.state.user.location ? locations[this.props.state.user.location].events.map(key => <option key={key} value={key}>{key}</option>) : ''}
                                </select>
        }

        return (
            <div className="account row">
                <div className="col-md-6">
                    <div className="content" dangerouslySetInnerHTML={forms[this.props.state.form].introductionHTML} />
                </div>
                <div className="col-md-6">
                    <form className="box" onSubmit={(e) => this.saveAndNext(e)}>
                        <span className="formSection">
                            <h3>Contact Information</h3>
                            <div dangerouslySetInnerHTML={forms[this.props.state.form].contactFormIntro} />
                        </span>
                        <div className="input-columns">
                            <input ref={(input) => this.firstName = input} 
                                   type="text" 
                                   placeholder="First Name" 
                                   required={true} 
                                   defaultValue={this.props.state.user.firstName} 
                                   name="fname"
                                   autoComplete="fname" />
                            <input ref={(input) => this.lastName = input} 
                                   type="text" 
                                   placeholder="Last Name" 
                                   required={true} 
                                   defaultValue={this.props.state.user.lastName} 
                                   name="lname"
                                   autoComplete="lname" />
                            <input ref={(input) => this.email = input} 
                                   type="email" 
                                   placeholder="Email" 
                                   required={true} 
                                   defaultValue={this.props.state.user.email} 
                                   name="email"
                                   autoComplete="email" />
                            <input ref={(input) => this.confirmEail = input} 
                                   type="email" 
                                   placeholder="Confirm Email" 
                                   required={true} 
                                   name="emailC"
                                   autoComplete="email" />
                            <Cleave ref={(input) => this.phone = input} 
                                    placeholder="Phone" 
                                    pattern="^\d{3}-\d{3}-\d{4}$"
                                    required={true} 
                                    value={this.props.state.user.phone} 
                                    options={{blocks: [3,3,4], delimiter: '-', numericOnly: true}}
                                    name="phone"
                                    type="tel"
                                    autoComplete="tel" />
                        </div>
                        <span className="formSection">
                            <h3>Choose a Profile Location</h3>
                            <div dangerouslySetInnerHTML={forms[this.props.state.form].locationFormIntro} />
                        </span>
                        <select ref={(input) => this.location = input} 
                                defaultValue={this.props.state.user.location} 
                                required={true}
                                onChange={(e) => this.handleLocationChange(e)}>
                            <option value="">Select a Location</option>
                            {Object.keys(locations).map(key => <option key={key} value={key}>{locations[key].title}</option>)}
                        </select>
                        {eventSelectOptions}
                        <input className="btn-submit" type="submit" value={this.props.state.form === 'join-now' ? 'Create Account' : 'Submit'} />
                        <p className="legal">By submitting this information, you agree to our <a rel="noopener noreferrer" target="_blank" href="https://www.profileplan.net/privacy-policy-terms/">Privacy Policy & Terms</a>.</p>
                    </form>
                </div>
            </div>
        );
    }
}

export default FormAccount;
