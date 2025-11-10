import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';

export const PortfolioProductSearch = (props) => {

    if(props.targetPictureId == -1){
        return (
            <div></div>
        );
    }

    return (
        <div>
            Search for products for Picture {props.targetPictureId}
            <input type="text" />
        </div>
    )
}