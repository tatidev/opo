import React from 'react';
import ReactDOM from 'react-dom';
import {ProjectsGallery} from "./ProjectsGallery";
let container = document.getElementById('ReactDOMContainer');
import {BaseURL} from "../../services/base";

function getPermissions(){
    $.ajax({
        url: BaseURL + '/permits/get',
        dataType: 'json',
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        success: function (data, msg) {
            ReactDOM.render(<ProjectsGallery permissions={data}/>, container);
        }
    });
}

getPermissions()
