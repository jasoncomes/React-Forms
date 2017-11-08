import React, { Component } from 'react';
import Cleave from 'cleave.js/react';
import { formatPrice } from '../helpers';
import 'whatwg-fetch'

import states from '../data/states';
import memberships from '../data/memberships';


class FormPayment extends Component {


    // Construct.
    constructor() {
        super()

        // Atempt Count State.
        this.state = {
            attemptCount: 0
        }

        // Bind methods.
        this.saveAndNext = this.saveAndNext.bind(this)
        this.ajaxAauthorizeNet = this.ajaxAauthorizeNet.bind(this)
        this.ajaxSuccessAndContinue = this.ajaxSuccessAndContinue.bind(this)
    }


    // Checkout Items/Prices/Discounts/Taxes
    componentWillMount() {

        // User State.
        const discounts       = this.props.state.discounts;
        const locations       = this.props.state.locations;
        const user            = this.props.state.user;
        const membershipPrice = memberships[user.membership].price;
        let prices            = [];

        // Discount ~ Location Price.
        if (user.location && discounts.locations && discounts.locations.hasOwnProperty(user.location)) {
            prices.push({
                type: 'Location Offer',
                price: discounts.locations[user.location]
            })
        }

        // Discount ~ Limited Time Price.
        if (discounts.limited) {
            prices.push({
                type: 'Limited Time Offer',
                price: discounts.limited
            })
        }

        // Discount ~ Promo Price.
        if (user.promo && discounts.promos) {
            discounts.promos.map((promo) => {
                if (promo.code.toLowerCase() !== user.promo.toLowerCase()) {
                    return false
                }   

                prices.push({
                    type: 'Promotional Offer: ' + promo.title + ' (' + promo.code + ')', 
                    price: promo.price
                })
                return false
            })
        }

        // Lowest Price available.
        let discount = {
            type: '',
            price: 0
        }

        for (let key in prices) {
            if (prices[key].price < membershipPrice) {
                discount.price = prices[key].price;
                discount.type  = prices[key].type;
            }
        }

        // Set New Price
        const price = discount.price ? discount.price : membershipPrice;

        // Discount Savings.
        discount.price = discount.price ? membershipPrice - discount.price : 0;

        // State Tax.
        const taxRate = locations[user.location].taxRate[price] ? locations[user.location].taxRate[price] : locations[user.location].taxRate.default;
        const taxTotal = price * taxRate / 100;

        // Total Price w/tax.
        const total = price + taxTotal;
        
        // Update Payment State.
        this.props.saveState({
            payment: {
                ...this.props.state.payment, 
                price,
                taxRate,
                taxTotal,
                total,
                discount,
            }
        });
    }


    // Save & Next Button Event.
    saveAndNext(ev) {

        // Prevent Default Submit.
        ev.preventDefault()

        // Update User State.
        this.props.saveState({
            payment: {
                ...this.props.state.payment,
                cardNumber: this.cardNumber.element.value,
                cardExpiration: this.cardExpiration.element.value,
                cvc: this.cvc.element.value,
            },
            user: {
                ...this.props.state.user,
                address: this.address.value,
                city: this.city.value,
                zip: this.zip.element.value,
                state: this.st.value
            }
        })

        // AuthorizeNet.
        this.ajaxAauthorizeNet()
    }


    // Authorize.Net Call.
    ajaxAauthorizeNet() {

        // Variables.
        const paymentBox = document.querySelector('.payment')
        const errorBox = document.getElementById('errorMessage')

        // Show Loader.
        paymentBox.classList.add('is-loading')

        // AttemptCount State update.
        this.setState((prevState) => {
            return { attemptCount: prevState.attemptCount + 1 };
        });

        fetch(this.props.state.ajax.url, {
            method: 'post',  
            headers: new Headers({  
              'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
            }),
            body: this.props.parseObjectToString({
                action: 'authorize_transation',
                _security_nonce: this.props.state.ajax.nonce,
                member_id: this.props.state.user.id,
                membership: this.props.state.user.membership,
                total: this.props.state.payment.total.toFixed(2),
                cardNumber: this.cardNumber.getRawValue(),
                cardExpiration: this.cardExpiration.element.value,
                cvc: this.cvc.element.value,
                firstName: this.props.state.user.firstName,
                lastName: this.props.state.user.lastName,
                email: this.props.state.user.email,
                phone: this.props.state.user.phone,
                address: this.address.value,
                city: this.city.value,
                zip: this.zip.element.value,
                state: this.st.value
            })
        })
        .then((resp) => resp.json())
        .then((results) => {
            // Hide Loader.
            paymentBox.classList.remove('is-loading')

            // If Failed.
            if (!results.success) {

                // Attempt Count Reached.
                if (this.state.attemptCount === 3) {
                    this.ajaxSuccessAndContinue(results)
                    return;
                }

                errorBox.classList.add('is-active')
                errorBox.innerHTML = results.errorMessage
                return;
            }

            // Remove ErrorMessage.
            errorBox.classList.remove('is-active')

            // Continue.
            this.ajaxSuccessAndContinue(results)
        })
        .catch((error) => {
            console.error(error);

            // Attempt Count Reached.
            if (this.state.attemptCount === 3) {
                this.ajaxSuccessAndContinue({transactionId: null, success: 0})
                return;
            }
        });
    }


    // AJAX Success - Update Authorize State - AJAX Wordpress DB - Next Step.
    ajaxSuccessAndContinue(response) {

        // Save Authorize State.
        this.props.saveState({
          authorize: {
            ...this.props.state.authorize,
            id: response.transactionId,
            success: response.success ? 1 : 0,
            attemptFailure: !response.success ? 1 : 0
          }
        })

        // AJAX Wordpress DB
        this.props.ajaxWordpressDB({
            authorize_id: response.transactionId,
            payment_status: response.success ? 1 : 0,
            attempt_failure: !response.success ? 1 : 0,
            total: this.props.state.payment.total.toFixed(2),
            address: this.address.value,
            city: this.city.value,
            zip: this.zip.element.value,
            state: this.st.value,
        })

        // AJAX Email
        this.props.ajaxEmail({
            member_id: this.props.state.user.id,
            first_name: this.props.state.user.firstName,
            last_name: this.props.state.user.lastName,
            email: this.props.state.user.email,
            address: this.address.value,
            city: this.city.value,
            state: this.st.value,
            zip: this.zip.element.value,
            authorize_id: response.transactionId,
            authorize_code: response.authCode,
            authorize_method: response.authMethod,
            membership: this.props.state.user.membership,
            membership_name: memberships[this.props.state.user.membership].name,
            payment_price: this.props.state.payment.price.toFixed(2),
            payment_taxRate: this.props.state.payment.taxRate,
            payment_taxTotal: this.props.state.payment.taxTotal.toFixed(2),
            payment_total: this.props.state.payment.total.toFixed(2),
        })

        // Next Step.
        this.props.nextStep()
    }


    // Render.
    render() {
        let discountHTML;
        if (this.props.state.payment.discount.price) {
            discountHTML = <tr>
                                <td>Your Savings <small>{this.props.state.payment.discount.type}</small></td>
                                <td>(${this.props.state.payment.discount.price})</td>
                            </tr>
        }

        return (
            <div className="payment row">
                <div className="col-md-6">
                    <div className="content">
                        <h3>Membership Summary</h3>
                        <table>
                            <tbody>
                                <tr>
                                    <td>{memberships[this.props.state.user.membership].name} Membership</td>
                                    <td>${memberships[this.props.state.user.membership].price}</td>
                                </tr>
                                <tr>
                                    <td colSpan="2" className="summary" dangerouslySetInnerHTML={memberships[this.props.state.user.membership].summaryHTML}></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                {discountHTML}
                                <tr>
                                    <td>State and Local Sales Tax</td>
                                    <td>({this.props.state.payment.taxRate}%) {formatPrice(this.props.state.payment.taxTotal)}</td>
                                </tr>
                                <tr className="total">
                                    <td>Your Total</td>
                                    <td>{formatPrice(this.props.state.payment.total)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div className="col-md-6">
                    <form className="box" onSubmit={(e) => this.saveAndNext(e)}>
                        <div className="loader"></div>
                        <span className="formSection">
                            <h3>Payment Information</h3>
                            <p>After your payment is confirmed, Profile by Sanford will contact you to schedule your first coaching appointment.</p>
                        </span>
                        <Cleave ref={(input) => this.cardNumber = input} 
                                placeholder="Card Number" 
                                required={true} 
                                options={{creditCard: true}}
                                name="cardnumber"
                                type="tel"
                                autoComplete="cc-number" />
                        <div className="input-columns">
                            <Cleave ref={(input) => this.cardExpiration = input} 
                                    placeholder="MM/YYYY"
                                    pattern="\d{1,2}/\d{4}"
                                    required={true}
                                    options={{date: true, datePattern: ['m', 'Y']}}
                                    name="exp-date"
                                    type="tel"
                                    autoComplete="cc-exp" />
                            <Cleave ref={(input) => this.cvc = input} 
                                    placeholder="Security Code" 
                                    required={true}
                                    pattern="[0-9]{3}"
                                    maxLength="3"
                                    options={{numericOnly: true}}
                                    name="cvc"
                                    type="tel"
                                    autoComplete="cc-csc" />
                        </div>
                        <input ref={(input) => this.address = input} 
                               type="text" 
                               placeholder="Street Address" 
                               required={true}
                               defaultValue={this.props.state.user.address} 
                               name="address"
                               autoComplete="billing street-address" />
                        <div className="input-columns">
                            <input ref={(input) => this.city = input} 
                                   type="text" 
                                   placeholder="City" 
                                   required={true}
                                   defaultValue={this.props.state.user.city} 
                                   name="city"
                                   autoComplete="billing address-level2" />
                            <div className="input-columns">
                                <select ref={(input) => this.st = input} 
                                        defaultValue={this.props.state.user.state} 
                                        required={true}
                                        name="state"
                                        autoComplete="billing address-level1">
                                    <option value="">State</option>
                                    {Object.keys(states).map(key => <option key={key} value={key}>{states[key]}</option>)}
                                </select>
                                <Cleave ref={(input) => this.zip = input} 
                                        placeholder="Zip Code" 
                                        required={true}
                                        pattern="[0-9]{5}"
                                        maxLength="5"
                                        options={{numericOnly: true}}
                                        name="zip"
                                        type="tel"
                                        autoComplete="billing postal-code" />
                            </div>
                        </div>
                        <input className="btn-submit" type="submit" value="Submit Payment" />
                        <div id="errorMessage">Credit card number is invalid.</div>
                    </form>
                </div>
                <div className="col-md-12">
                    <p className="note">Profile by Sanford uses a secure, third-party payment processing service. All transactions are one-time transactions and no credit card information is stored. Memberships do not automatically renew at the end of membership term. Profile by Sanford will issue a refund for the purchase price of any membership within three days of purchase. Memberships are non-transferable.</p>
                </div>
            </div>
        );
    }
}

export default FormPayment;
