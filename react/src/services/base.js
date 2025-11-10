import axios from "axios";

// Legacy Code
// export const BaseURL = window.location.href.search("dev.") > -1 ? 'https://dev.opuzen.com/dev/pms/' : 'https://app.opuzen.com/pms/';
const url = new URL(window.location.href);
export const BaseURL = url.port
  ? `${url.protocol}//${url.hostname}:${url.port}`
  : `${url.protocol}//${url.hostname}`;


const API = axios.create({
    baseURL: BaseURL,
    responseType: 'json',
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    }
});

const getRequestConfiguration = (authorization) => {
    const headers = {
        // 'Accept': 'application/json, text/javascript, */*; q=0.01',
        // 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    };
    if (authorization) headers.Authorization = `Bearer ${authorization}`;
    return { headers };
};

export const makeRequest = ({
                                url,
                                values = null,
                                successCallback = () => {},
                                failureCallback = () => {},
                                requestType = 'POST',
                                authorization = null,
                            }) => {

    const requestConfiguration = getRequestConfiguration(authorization);
    let promise;

    switch (requestType) {
        case 'GET':
            if(values == null){
                promise = API.get(url, requestConfiguration)
            } else {
                promise = API.get(url, {params: values}, requestConfiguration);
            }
            break;
        case 'POST':
            promise = API.post(url, values, requestConfiguration);
            break;
        case 'DELETE':
            if(values == null){
                promise = API.delete(url, requestConfiguration)
            } else {
                promise = API.delete(url, {params: values}, requestConfiguration);
            }
            break;
        default:
            return;
    }

    promise
        .then((response) => {
            const {data} = response;
            successCallback(data);
        })
        .catch((error) => {
            if (error.response) {
                failureCallback(error.response.data);
            }
        });
}
