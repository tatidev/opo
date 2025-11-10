import React from 'react';

export const Loader = (props) => {
    return (
        <div className={"fa-3x mx-4 " + props.className}>
            <i className={"fas fa-circle-notch fa-spin"}></i>
        </div>
    )
}