import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';
import Collapse from 'react-bootstrap/Collapse'

import '../css/style.css';

import {MY_Pagination} from "./Pagination";
import {PortfolioProductSearch} from "./PortfolioProductSearch";
import {PortfolioForm} from "./PortfolioForm";
import {AsyncTypeaheadInput} from "./AsyncTypeaheadInput";
import {PortfolioGalleryPicture} from "./PortfolioGalleryPicture";
import {getPictures, addPicture, uploadImage} from "../services/portfolioService";
import {BaseURL, makeRequest} from "../services/base";

const Portfolio = () => {
    const [isLoading, setIsLoading] = useState(true);
    const [visibleForm, setVisibleForm] = useState(false);
    const [targetPictureID, setTargetPictureID] = useState(-1);
    const [data, setData] = useState({result: [], totalCount: 0});
    const [params, setParams] = useState({
        search: '',
        page: 0,
        size_per_page: 8,
    });

    const fetchData = async () => {
        setIsLoading(true);
        const formData = new FormData()
        formData.set('page', params.page);
        formData.set('size_per_page', params.size_per_page);
        makeRequest({
            url: 'portfolio/get_pictures',
            values: formData,
            successCallback: (data) => {
                setData({result: data.result, totalCount: data.total_count});
            },
            failureCallback: (e) => console.log(e)
        });
        setIsLoading(false);
    }

    useEffect(() => {
        fetchData();
    }, [params]);

    const refreshParams = (action) => {
        setVisibleForm(false);
        setParams({...params, ...action});
        setTargetPictureID(-1);
    }

    const handlePageChange = (new_page) => refreshParams({page: new_page});

    const toggleActive = (status) => (status == '1' ? '0' : '1');

    const handlePictureStatusToggle = (picture_id) => {
        const formData = new FormData();
        formData.set('picture_id', picture_id);
        const result_ = data.result.map((item, ix) => {
            if(item.id == picture_id){
                formData.set('status', toggleActive(item.active));
                item.active = toggleActive(item.active);
                return item;
            }
            return item;
        });
        makeRequest({
            url: 'portfolio/status_picture',
            values: formData,
            successCallback: (ret) => {
                setData({...data, result: result_});
            },
            failureCallback: (e) => console.log(e)
        });

    }

    const handleSubmit = (formData, callback) => {
        makeRequest({
            url: 'portfolio/add_picture',
            values: formData,
            successCallback: (imgData) => {
                console.log("NewImgData", imgData);
                const result_ = [imgData].concat(data.result);
                setData({result: result_, totalCount: data.totalCount+1});
                setVisibleForm(false);
                callback();
            },
            failureCallback: (e) => console.log(e)
        });
    }

    const handleProductPictureAdd = (product_name, product_id, picture_id) => {
        const formData = new FormData();
        formData.set('item_id', product_id);
        formData.set('picture_id', picture_id);
        makeRequest({
            url: 'portfolio/add_product',
            values: formData,
            successCallback: (ret) => {
                const result_ = [...data.result].map((item, ix) => {
                        if(item.id == picture_id){
                            return {
                                ...item,
                                products_assoc_id: item.products_assoc_id.concat(product_id),
                                products_assoc: item.products_assoc.concat(product_name)
                            }
                        }
                        else {
                            return item;
                        }
                    }
                );
                setData({...data, result: result_});
                setTargetPictureID(-1);
            },
            failureCallback: (e) => console.log(e)
        });

    }

    const handleProductPictureDelete = (product_id, picture_id) => {
        const formData = new FormData();
        formData.set('item_id', product_id);
        formData.set('picture_id', picture_id);
        makeRequest({
            url: 'portfolio/delete_product',
            values: formData,
            successCallback: (ret) => {
                const result_ = [...data.result].map((item, ix) => {
                        if(item.id == picture_id){
                            const product_ix_to_delete = item.products_assoc_id.findIndex((e) => e == product_id);
                            return {
                                ...item,
                                products_assoc_id: item.products_assoc_id.filter((id, ix) => ix !== product_ix_to_delete),
                                products_assoc: item.products_assoc.filter((id, ix) => ix !== product_ix_to_delete)
                            }
                        }
                        else {
                            return item;
                        }
                    }
                )
                setData({...data, result: result_});
            },
            failureCallback: (e) => console.log(e)
        });

    }

    const handlePictureDelete = (picture_id) => {
        const formData = new FormData();
        formData.set('picture_id', picture_id);
        makeRequest({
            url: 'portfolio/delete_picture',
            values: formData,
            successCallback: (ret) => {
                setData({...data, result: data.result.filter((item, ix) => item.id !== picture_id)});
            },
            failureCallback: (e) => console.log(e)
        });

    }

    const openNewPictureForm = () => {
        setVisibleForm(!visibleForm);
        setTargetPictureID(-1);
        console.log("Open", !visibleForm);
    }

    const openProductSearch = (picture_id) => {
        setVisibleForm(false);
        setTargetPictureID(picture_id);
    }

    if(isLoading){
        return (
            <div>
                Loading..
            </div>
        )
    }
    else {
        console.log(data);
        return (
            <div>
                <div className={"row"}>
                    <div className={"col-6"}>
                        <button className={"btn btn-outline-success no-border"}
                                onClick={() => openNewPictureForm()}
                                aria-controls={"frmNewPictureDiv"}
                                aria-expanded={visibleForm} >
                            Add New Picture <i className={"fas fa-plus-circle"}></i>
                        </button>
                    </div>
                    <div className={"col-6"}>
                        <MY_Pagination
                            class={"float-right"}
                            totalCount={data.totalCount}
                            size_per_page={params.size_per_page}
                            page={params.page}
                            onPageChange={handlePageChange} />
                    </div>
                    <div className={"col-12"}>
                        <Collapse in={visibleForm} >
                            <div id={"frmNewPictureDiv"}>
                                <PortfolioForm
                                    formID={"frmNewPicture"}
                                    handleSubmit={handleSubmit} />
                            </div>
                        </Collapse>
                    </div>
                    <div className={"col-12"}>
                        <AsyncTypeaheadInput
                            hide={targetPictureID==-1}
                            targetPictureId={targetPictureID}
                            onSelect={(product_name, product_id, picture_id) => handleProductPictureAdd(product_name, product_id, picture_id)} />
                    </div>
                </div>
                <div>
                    <div id={"PortfolioGallery"} className="pictures-container d-flex flex-wrap align-items-start">
                        {
                            data.result.length > 0 ?
                                data.result.map(row => {
                                    return (
                                        <PortfolioGalleryPicture
                                            key={row.id}
                                            data={row}
                                            onPictureStatusToggle={(picture_id) => handlePictureStatusToggle(picture_id)}
                                            onProductPictureAdd={(picture_id) => openProductSearch(picture_id)}
                                            onProductPictureDelete={handleProductPictureDelete}
                                            onPictureDelete={handlePictureDelete} />
                                    )
                                })
                                : <div>No data</div>
                        }
                    </div>
                </div>
            </div>
        )
    }


};

let container = document.getElementById('ReactDOMContainer');
ReactDOM.render(<Portfolio />, container);