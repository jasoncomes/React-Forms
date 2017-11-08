import React, { Component } from 'react';

import forms from '../data/forms';


class FormCompletion extends Component {

    // Render
    render() {
        return (
            <div className="completion row">
                <div className="col-md-8 col-md-offset-2" dangerouslySetInnerHTML={this.props.state.form === 'join-now' && !this.props.state.authorize.success ? forms[this.props.state.form].failHTML : forms[this.props.state.form].completionHTML} />
            </div>
        )
    }
}

export default FormCompletion;
