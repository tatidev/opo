import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';
import Collapse from 'react-bootstrap/Collapse'
import ReactTooltip from 'react-tooltip'
var _ = require('lodash');

import '../../css/style.css';

import {ProjectForm} from "./ProjectForm";
import {Project} from "./Project";
import {MY_Pagination} from "../Pagination";
import {Loader} from "../Loader";
import {makeRequest} from "../../services/base";

export const ProjectsGallery = (props) => {
    // console.log("ProjectsGallery", props.permissions, typeof(props.permissions));
    let canEdit = _.find(props.permissions, {module:"portfolio", action:"edit"}) != undefined;
    // console.log("canEdit", canEdit);
    
    const [isLoading, setIsLoading] = useState(true);
    const [visibleForm, setVisibleForm] = useState(false); // Open new Project form

    const [data, setData] = useState({
        result: [], // List of Project IDs being rendered
        totalCount: -1 // Total Projects in DB
    });
    const [params, setParams] = useState({
        search: '',
        page: 0,
        size_per_page: 8,
    });

    const fetchData = async () => {
        setIsLoading(true);
        setVisibleForm(false);
        makeRequest({
            url: 'portfolio/get',
            requestType: 'GET',
            values: {search: params.search, page: params.page, size_per_page: params.size_per_page},
            successCallback: (data) => {
                setData({result: data.result, totalCount: data.total_count});
                setIsLoading(false);
                setTimeout(() => ReactTooltip.rebuild(), 3000);
            },
            failureCallback: (e) => {
                console.log(e);
                setIsLoading(false);
            }
        });

    }

    useEffect(() => {
        const delayId = setTimeout(() => fetchData(), 700);
        return () => clearTimeout(delayId);
    }, [params]);

    const refreshParams = (action) => {
        setParams({...params, ...action});
    }

    const handleSearchChange = (searchText) => refreshParams({search: searchText});
    const handlePageChange = (new_page) => refreshParams({page: new_page});

    const handleSubmitProject = (formValues, formCallback) => {
        // const files = formReturn.files;
        // const values = formValues.values;
        const formData = new FormData();
        formData.append('name', formValues.name);
        formData.append('notes', formValues.notes);
        console.log("Submitted", formValues);
        // return;
        makeRequest({
            url: 'portfolio/add_project',
            values: formData,
            successCallback: (ProjectData) => {
                console.log("ProjectData", ProjectData);
                const result_ = [ProjectData.id].concat(data.result);
                setData({result: result_, totalCount: data.totalCount+1});
                setVisibleForm(false);
                formCallback(); // Cleans form after success
            },
            failureCallback: (e) => console.log(e)
        });
    }

    const handleDeleteProject = (project_id) => {
        const formData = new FormData();
        formData.append('project_id', project_id);
        makeRequest({
            url: 'portfolio/delete_project',
            values: formData,
            successCallback: (ret) => {
                console.log("Delete return", ret);
                const result_ = data.result.filter((id, ix) => id !== project_id);
                setData({result: result_, totalCount: data.totalCount-1});
            },
            failureCallback: (e) => console.log(e)
        });

        const result_ = [...data.result].filter((val, ix) => val !== project_id);
        setData({...data, result: result_});
    }

    if(isLoading){
        return (
            <Loader className={"internal-loader-spin"} />
        )
    }
    else {
        console.log("Gallery", data);
        return (
            <div>
                <div className={"row"}>
                    <div className={"col-12"}>

                        <div className="alert alert-success alert-dismissible fade show" role="alert">
                            <h4 className="alert-heading">Important Information</h4>
                            <p>
                                All images in this Portfolio have been collected from different sources (Instagram, sent by clients directly, from hotel websites, purchased from photographers, etc.) for social media purposes and for client requests only.
                                <br/>
                                DO NOT use these images for publishing or any other marketing purposes other than social media posts. We don’t own the copyrights of most of the photographs.
                                <br/>
                                When sharing the photos in Social Media,  ALWAYS make sure to give credit to the designers and the photographer. This means mentioning them in the caption and/or tagging them. Unless, this information is unknown.
                            </p>
                            {/*<button type="button" className="close" data-dismiss="alert" aria-label="Close">*/}
                            {/*    <span aria-hidden="true">&times;</span>*/}
                            {/*</button>*/}
                        </div>

                    </div>
                </div>
                <div className={"row"}>
                    <div className={"col-12"}>
                        <div className={"input-group col-12 px-0"} style={{
                            borderBottom: '1px dotted #bfac02',
                            boxShadow: '0 1px 2px rgba(0,0,0,0.1) inset'
                        }}>
                            <div className={"input-group-prepend"}>
                                <span className={"input-group-text"} style={{
                                    backgroundColor: 'transparent',
                                    border: 'none',
                                    fontSize: '30px'
                                }}>
                                    <i className={"fas fa-search"}></i>
                                </span>
                            </div>
                            <input id="input_search"
                                   type="text"
                                   placeholder="Search"
                                   className={"form-control input_search"}
                                   value={params.search}
                                   onChange={(el) => handleSearchChange(el.target.value)}
                                   />
                        </div>
                    </div>
                </div>
                <div className={"row"}>
                    <div className={"col"}>
                        {canEdit &&
                        <button className={"btn btn-outline-success no-border"}
                                onClick={() => setVisibleForm(!visibleForm)}
                                aria-controls={"frmNewProjectDiv"}
                                aria-expanded={visibleForm}>
                            Add New Project <i className={"fas fa-plus-circle"}></i>
                        </button>
                        }
                    </div>
                    <div className={"col"}>
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
                                <ProjectForm
                                    formID={"frmNewProject"}
                                    handleSubmit={handleSubmitProject} />
                            </div>
                        </Collapse>
                    </div>
                </div>
                <div>
                    <div className="pictures-container d-flex flex-wrap align-items-start">
                        {
                            data.result.length > 0 ?
                                data.result.map(id => {
                                    return (
                                        <Project
                                            key={id}
                                            id={id}
                                            onOpenProductSearch={(callback) => openProductSearch(callback)}
                                            onProjectDelete={() => handleDeleteProject(id)}
                                            canEdit={canEdit}
                                        />
                                    )
                                })
                                : <div>No data</div>
                        }
                    </div>
                </div>

                <ReactTooltip />
            </div>
        )
    }


};
