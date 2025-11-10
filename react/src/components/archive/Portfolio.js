import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';
import Collapse from 'react-bootstrap/Collapse'

import '../css/style.css';

import {MY_Pagination} from "./Pagination";
import {PortfolioProductSearch} from "./PortfolioProductSearch";
import {PortfolioProjectForm} from "./PortfolioProjectForm";
import {AsyncTypeaheadInput} from "./AsyncTypeaheadInput";
import {PortfolioProject} from "./PortfolioProject";
import {BaseURL, makeRequest} from "../services/base";

const Portfolio = () => {
    const [isLoading, setIsLoading] = useState(true);
    const [visibleForm, setVisibleForm] = useState(false);
    const [targetPictureID, setTargetPictureID] = useState(-1);
    const [data, setData] = useState({result: [], totalCount: -1});
    const [params, setParams] = useState({
        search: '',
        page: 0,
        size_per_page: 8,
    });

    const fetchData = async () => {
        setIsLoading(true);
        makeRequest({
            url: 'portfolio/get_projects',
            requestType: 'GET',
            values: {page: params.page, size_per_page: params.size_per_page},
            successCallback: (data) => {
                setData({result: data.result, totalCount: data.total_count});
                setIsLoading(false);
            },
            failureCallback: (e) => {
                console.log(e);
                setIsLoading(false);
            }
        });

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

    const handleSubmitProject = (formData, formCallback) => {
        console.log("Submitted", formData);
        makeRequest({
            url: 'portfolio/add_project',
            values: formData,
            successCallback: (ProjectData) => {
                console.log("ProjectData", ProjectData);
                const result_ = [ProjectData].concat(data.result);
                setData({result: result_, totalCount: data.totalCount+1});
                setVisibleForm(false);
                formCallback();
            },
            failureCallback: (e) => console.log(e)
        });
    }

    const openNewProjecteForm = () => {
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
            <div className={"fa-3x internal-loader-spin mx-4"}>
                <i className={"fas fa-circle-notch fa-spin"}></i>
            </div>
        )
    }
    else {
        console.log("Got data", data);
        return (
            <div>
                <div className={"row"}>
                    <div className={"col-6"}>
                        <button className={"btn btn-outline-success no-border"}
                                onClick={() => openNewProjecteForm()}
                                aria-controls={"frmNewProjectDiv"}
                                aria-expanded={visibleForm} >
                            Add New Project <i className={"fas fa-plus-circle"}></i>
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
                            <div id={"frmNewProjectDiv"}>
                                <PortfolioProjectForm
                                    formID={"frmNewProject"}
                                    handleSubmit={handleSubmitProject} />
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
                    <div id={"ProjectGallery"} className="pictures-container d-flex flex-wrap align-items-start">
                        {
                            data.result.length > 0 ?
                                data.result.map(row => {
                                    return (
                                        <PortfolioProject
                                            key={row.id}
                                            data={row}
                                            onProjectStatusToggle={(id) => handleProjectStatusToggle(id)}
                                            onProjectDelete={() => handleProjectDelete(row.id)} />
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