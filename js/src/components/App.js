import React, { Component } from 'react';
import 'whatwg-fetch'

import FormAccount from './FormAccount';
import FormMemberships from './FormMemberships';
import FormPayment from './FormPayment';
import FormCompletion from './FormCompletion'

import forms from '../data/forms';


class App extends Component {

    constructor() {
        super();

        // Form
        const form = document.getElementById('webform').getAttribute('data-form');

        // Bind Methods
        this.saveState           = this.saveState.bind(this)
        this.nextStep            = this.nextStep.bind(this)
        this.renderSteps         = this.renderSteps.bind(this)
        this.renderForm          = this.renderForm.bind(this)
        this.parseObjectToString = this.parseObjectToString.bind(this)
        this.ajaxWordpressDB     = this.ajaxWordpressDB.bind(this)
        this.ajaxEmail           = this.ajaxEmail.bind(this)

        // Initial State
        this.state = {
            ajax: {
                url: document.getElementById('webform').getAttribute('data-ajaxurl'),
                nonce: document.getElementById('webform').getAttribute('data-nonce')
            },
            form,
            step: 1,
            user: {
                id: '',
                firstName: '',
                lastName: '',
                email: '',
                phone: '',
                address: '',
                city: '',
                state: '',
                zip: '', 
                employer: '',
                location: '',
                event: '',
                membership: form === 'join-now' ? 'MF-1001' : '', // Default Membership
                promo: '',
            },
            payment: {
                price: 0,
                taxRate: 0,
                taxTotal: 0,
                total: 0,
                discount: 0,
                cardNumber: '',
                cardExpiration: '',
                cvc: '',
            },
            authorize: {
                id: 0,
                success: 0,
                attemptFailure: 0,
            },
            locations: {},
            employers: {},
            discounts: {}
        };
    }


    // External Locations/Events/Employer Data
    componentWillMount() {

        // Ajax Datapoints Fetch.
        fetch(this.state.ajax.url, {
            method: 'post',  
            headers: new Headers({  
              'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
            }),
            body: this.parseObjectToString({
                action: 'webform_data_transaction',
                _security_nonce: this.state.ajax.nonce,
            })
        })
        .then((resp) => resp.json())
        .then((results) => {
            if (results && results['locations'] && results['partners']) {
                this.setState({
                    locations: results['locations'],
                    employers: results['partners'],
                    discounts: results['discounts']
                })
            }
        })
        .catch((error) => {
            console.error(error);
        });
    }


    // Save State from components props.
    saveState(updatedState) {

        // Get Keys
        Object
            .keys(updatedState)
            .map(key => {

                // Copy State
                let data = {...this.state[key]}

                // Update State
                data = updatedState[key];

                // Set State.
                this.setState({[key] : data});

                return true;
            })
    }


    // Parse Data Object to String
    parseObjectToString(paramsObject) {
        return Object
            .keys(paramsObject)
            .map(key => {
                const value = paramsObject[key] ? paramsObject[key] : '';
                return `${encodeURIComponent(key)}=${encodeURIComponent(value)}`
            })
            .join('&'); 
    }


    // AJAX - Wordpress DB Call.
    ajaxWordpressDB(data) {
        fetch(this.state.ajax.url, {
            method: 'post',
            headers: new Headers({  
              'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
            }),
            body: this.parseObjectToString(
                Object.assign({
                    action: 'wordpress_db_transaction',
                    _security_nonce: this.state.ajax.nonce,
                    member_id: this.state.user.id
                }, 
                    data
                )
            )
        })
        .then((resp) => resp.json())
        .then((results) => {
            // Set user id.
            if (!this.state.user.id && results.success) {

                // Copy User State.
                let user = {
                    ...this.state.user,
                    id: results.member_id
                }

                // Update User State with ID.
                this.setState({user})
            }
        })
        .catch((error) => {
            console.error(error);
        });
    }


    // AJAX - Email Call.
    ajaxEmail(data) {

        // Ajax Email.
        fetch(this.state.ajax.url, {
            method: 'post',  
            headers: new Headers({  
              'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
            }),
            body: this.parseObjectToString(
                Object.assign({
                    action: 'webform_mailer_transaction',
                    _security_nonce: this.state.ajax.nonce,
                    form: this.state.form
                }, 
                    data
                )
            )
        });
    }


    // Next step.
    nextStep() {
        // Top of page.
        window.scroll(0,0)

        // Next Step.
        this.setState((prevState) => {
            return { step: prevState.step + 1 };
        });
    }


    // Show Steps
    renderSteps() {
        if (this.state.form !== 'join-now' || this.state.step === 4) {
            return;
        }

        return (
            <ul className="steps" data-step={this.state.step}>
                <li><span>1.</span> <span>Create</span> Account</li>
                <li><span>2.</span> <span>Choose</span> Membership</li>
                <li><span>3.</span> <span>Complete</span> Purchase</li>
            </ul>
        )
    }


    // Show Form
    renderForm(step) {
        switch (step) {
            case '1':
            default:
                return <FormAccount state={this.state}
                                    saveState={this.saveState} 
                                    ajaxWordpressDB={this.ajaxWordpressDB} 
                                    ajaxEmail={this.ajaxEmail} 
                                    nextStep={this.nextStep} />
            case 2:
                return <FormMemberships state={this.state}
                                    saveState={this.saveState}
                                    ajaxWordpressDB={this.ajaxWordpressDB} 
                                    nextStep={this.nextStep} />
            case 3:
                return <FormPayment state={this.state} 
                                    setPayment={this.setPayment}
                                    saveState={this.saveState} 
                                    ajaxWordpressDB={this.ajaxWordpressDB} 
                                    ajaxEmail={this.ajaxEmail}
                                    parseObjectToString={this.parseObjectToString}
                                    nextStep={this.nextStep} />
            case 4:
                return <FormCompletion state={this.state}
                                       parseObjectToString={this.parseObjectToString} />
        }
    }
    

    // Render.
    render() {
        return (
            <div className="container">
                <div className="row">
                    <div className="col-md-8 col-md-offset-2">
                        <img className="logo" src="/wp-content/themes/profile/webforms/assets/img/logo.png" alt="Profile Plan" />
                        <h2 className="form-title">{forms[this.state.form].stepTitle[this.state.step]}</h2>
                        {this.renderSteps()}
                    </div>
                </div>
                {this.renderForm(this.state.step)}
            </div>
        )
    }
}

export default App;
