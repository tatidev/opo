import {BaseURL, makeRequest} from "./base";

export async function getProductSearch(props){
    $.ajax({
        method: "POST",
        url: BaseURL + 'product/typeahead_products_list',
        dataType: 'json',
        data: {...props.values, 'itemsOnly': true, 'includeDigital': true},
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        success: function (data, msg) {
            props.successCallback(data)
        }
    });
}

export async function getPermissions(props){
    $.ajax({
        url: BaseURL + '/permits/get',
        dataType: 'json',
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        success: function (data, msg) {
            props.successCallback(data)
        }
    });
}

// /**
//  * @param {{contentType: string, uploadUrl: string}} resourceData for upload your image
//  * @param {string} file path to file in filesystem
//  * @returns {boolean} true if data uploaded
//  */
// export async function uploadImage(resourceData, file, extraFormData={}) {
//     return new Promise((resolver, rejecter) => {
//         const xhr = new XMLHttpRequest();
//
//         xhr.onload = () => {
//             if (xhr.status < 400) {
//                 resolver(true)
//             } else {
//                 const error = new Error(xhr.response);
//                 rejecter(error)
//             }
//         };
//         xhr.onerror = (error) => {
//             rejecter(error)
//         };
//
//         xhr.open('POST', resourceData.uploadUrl);
//         xhr.setRequestHeader('Content-Type', resourceData.contentType);
//         xhr.send({ files: file, formData: extraFormData });
//     })
// }