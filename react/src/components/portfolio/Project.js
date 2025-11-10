import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';
import Carousel from 'react-bootstrap/Carousel'

import {Icons} from "./Icons";
import {Loader} from "../Loader";
import EditableLabel from '../EditableLabel';
import {AsyncTypeaheadInput} from "../AsyncTypeaheadInput";
import {makeRequest} from "../../services/base";

export const Project = (props) => {
    let inputFile = '';
    const [isLoading, setIsLoading] = useState(true);
    const [visibleProductSearch, setVisibleProductSearch] = useState(false);
    const [data, setData] = useState({pictures: []});
    const [currImgIndex, setImgIndex] = useState(0);

    const canEdit = props.canEdit;
    const isActive = () => data.active == undefined ? false : data.active == '1';
    const toggleStatus = (status) => status == '1' ? '0' : '1';

    const fetchData = async () => {
        setIsLoading(true);
        makeRequest({
            url: 'portfolio/get_project',
            requestType: 'GET',
            values: {
                project_id: props.id
            },
            successCallback: (r) => {
                // console.log("Project", props.id, r);
                setData(r);
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
    }, []);

    // Project Handlers
    const handleCarouselSelect = (selectedIndex, e) => {
        setVisibleProductSearch(false);
        setImgIndex(selectedIndex);
        // console.log("Carousel change", props.id, selectedIndex, data.pictures[selectedIndex].notes);
    }

    const handleChangeStatus = () => {
        if(!canEdit) return;
        setIsLoading(true);
        const new_status = toggleStatus(data.active);
        const formData = new FormData()
        formData.set('project_id', props.id);
        formData.set('active', new_status);
        // console.log("MakeRequest StatusChange", props.id, new_status);
        handleUpdateProject(formData, (r) => {
            setData({...data, active: new_status});
            setIsLoading(false);
        })
    }

    const handleChangeName = (new_name) => {
        if(!canEdit) return;
        setIsLoading(true);
        const formData = new FormData()
        formData.set('project_id', props.id);
        formData.set('name', new_name);
        // console.log("MakeRequest ChangeName", props.id, new_name);
        handleUpdateProject(formData, (r) => {
            setData({...data, name: new_name});
            setIsLoading(false);
        })
    }

    const handleChangeNotes = (new_notes) => {
        if(!canEdit) return;
        setIsLoading(true);
        const formData = new FormData()
        formData.set('project_id', props.id);
        formData.set('notes', new_notes);
        // console.log("MakeRequest ChangeNotes", props.id, new_notes);
        handleUpdateProject(formData, (r) => {
            setData({...data, notes: new_notes});
            setIsLoading(false);
        })
    }

    const handleUpdateProject = (formData, callback) => {
        makeRequest({
            url: 'portfolio/update_project',
            requestType: 'POST',
            values: formData,
            successCallback: callback,
            failureCallback: (e) => {
                console.log(e);
                setIsLoading(false);
            }
        });
    }

    // Picture Handlers
    const handleAddPicture = () => {
        if(!canEdit) return;
        setIsLoading(true);
        const filesList = inputFile.files;
        if(filesList.length == 0) return;

        const formData = new FormData();
        formData.set('project_id', props.id);
        formData.set('files', filesList[0]);
        // console.log("Upload", props.id, filesList[0]);
        makeRequest({
            url: 'portfolio/add_picture',
            requestType: 'POST',
            values: formData,
            successCallback: (r) => {
                if(r.success){
                    const pictures_ = [r.data].concat([...data.pictures]);
                    setData({...data, pictures: pictures_});
                }
                else {
                    console.log(r.error);
                    show_success_swal("Error", "warning");
                }
                setIsLoading(false);
            },
            failureCallback: (e) => {
                console.log(e);
                setIsLoading(false);
            }
        });
        // Clean
        inputFile.value = "";
    }
    const handleChangeProductNote = (picture_id, new_note) => {
        if(!canEdit) return;
        setIsLoading(true);
        const formData = new FormData()
        formData.set('picture_id', picture_id);
        formData.set('notes', new_note);
        // console.log("MakeRequest Product ChangeNotes", props.id, new_note);
        handleUpdatePicture(formData, (r) => {
            const pictures_ = [...data.pictures].map((item, ix) => {
                if(item.id == picture_id){
                    return {...item, notes: new_note};
                }
                else {
                    return item;
                }
            });
            setData({...data, pictures: pictures_});
            setIsLoading(false);
        })
    }

    const handleUpdatePicture = (formData, callback) => {
        makeRequest({
            url: 'portfolio/update_picture',
            requestType: 'POST',
            values: formData,
            successCallback: callback,
            failureCallback: (e) => {
                console.log(e);
                setIsLoading(false);
            }
        });
    }

    const handleAddProductPicture = (picture_id, selected) => {
        if(!canEdit) return;
        setIsLoading(true);
        const formData = new FormData()
        formData.set('picture_id', picture_id);
        formData.set('item_id', selected.id);
        // console.log("MakeRequest AddProductPicture", picture_id, selected);
        makeRequest({
            url: 'portfolio/add_product',
            requestType: 'POST',
            values: formData,
            successCallback: (r) => {
                const pictures_ = [...data.pictures].map((item, ix) => {
                        if(item.id == picture_id){
                            return {
                                ...item,
                                products_assoc_id: item.products_assoc_id.concat(selected.id),
                                products_assoc: item.products_assoc.concat(selected.name)
                            }
                        }
                        else {
                            return item;
                        }
                    }
                );
                setData({...data, pictures: pictures_});
                setIsLoading(false);
            },
            failureCallback: (e) => {
                console.log(e);
                setIsLoading(false);
            }
        });
    }

    const handleDeleteProductPicture = (picture_id, product_id) => {
        if(!canEdit) return;
        setIsLoading(true);
        const formData = new FormData()
        formData.set('picture_id', picture_id);
        formData.set('item_id', product_id);
        // console.log("MakeRequest DeleteProductPicture", picture_id, product_id);
        makeRequest({
            url: 'portfolio/delete_product',
            requestType: 'POST',
            values: formData,
            successCallback: (r) => {
                const pictures_ = [...data.pictures].map((item, ix) => {
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
                setData({...data, pictures: pictures_});
                setIsLoading(false);
            },
            failureCallback: (e) => {
                console.log(e);
                setIsLoading(false);
            }
        });
    }

    if(isLoading){
        return (
            <div key={props.id} className="card">
                <div className={"card-body"}>
                    <Loader />
                </div>
            </div>
        )
    }
    return (
        <div key={props.id} className="card">
            <Carousel activeIndex={currImgIndex} onSelect={handleCarouselSelect} interval={null}>
                {
                    data.pictures.length > 0 ?
                        data.pictures.map((row, ix) => {
                            return (
                                <Carousel.Item key={row.id}>
                                    <img
                                        className={"d-block w-100 card-img-top"}
                                        src={row.url}
                                        onClick={() => window.open(row.url, "_blank")}
                                    />
                                </Carousel.Item>
                            )
                        })
                        :
                        <Carousel.Item key={-1}>
                            <img
                                className={"d-block w-100 card-img-top"}
                                src={"https://fakeimg.pl/300x200/ffffff/bfac02/?text=Upload"}
                                onClick={() => canEdit && inputFile.click()}
                            />
                        </Carousel.Item>
                }
            </Carousel>
            <div className="card-body">
                <div className="card-text">
                    {
                        data.pictures.length == 0 ?
                            <>
                            </>
                            :
                            <div className={"d-flex flex-column"}>
                                {canEdit &&
                                <AsyncTypeaheadInput
                                    hide={!visibleProductSearch}
                                    placeholder={"Search products to add..."}
                                    onSelect={(r) => handleAddProductPicture(data.pictures[currImgIndex].id, r)}/>
                                }
                                <ProjectPicture
                                    project_id={props.id}
                                    {...data.pictures[currImgIndex]}
                                    handleChangeProductNote={handleChangeProductNote}
                                    setVisibleProductSearch={setVisibleProductSearch}
                                    handleDeleteProductPicture={handleDeleteProductPicture}
                                    canEdit={canEdit}
                                />
                            </div>
                    }
                </div>
                <div className={"card-text d-flex flex-wrap my-3"}>
                    <span className={"portfolio-box-title"}><b>Project name</b></span>
                    <EditableLabel
                        initialValue={data.name}
                        labelClass={"project-name-label w-100"}
                        inputClass={"project-name-input w-100"}
                        inputMaxLength={200}
                        save={handleChangeName}
                        canEdit={canEdit}
                    />
                    <span className={"portfolio-box-title"}><b>Project Notes</b></span>
                    <EditableLabel
                        initialValue={data.notes.length > 0 ? data.notes : "(no notes)"}
                        labelClass={"project-notes-label w-100"}
                        inputClass={"project-notes-input w-100"}
                        save={handleChangeNotes}
                        canEdit={canEdit}
                    />
                </div>
                {canEdit &&
                <div className="d-flex justify-content-between mt-2">
                    {Icons.Status(isActive(), () => handleChangeStatus())}
                    {Icons.Add(() => inputFile.click(), "Add Picture")}
                    {Icons.Trash(() => {
                        show_swal(
                            {},
                            {
                                title: `Are you sure you want to delete the project ${data.name}?`
                            },
                            {
                                complete: () => props.onProjectDelete()
                            }
                        );
                    })}
                </div>
                }
                <div className={"hide"}>
                    {canEdit &&
                    <input type="file" onChange={handleAddPicture} ref={input => inputFile = input}/>
                    }
                </div>
            </div>
        </div>
    )
}

const ProjectPicture = (props) => {
    // console.log("ProjectPicture", props);
    return (
        <div className={"d-flex flex-wrap my-3"}>
            <span className={"portfolio-box-title"}><b>Picture notes</b> {Icons.ProjectNotes()}</span>
            <EditableLabel
                inputId={props.project_id+"-"+props.id}
                inputName={props.project_id+"-"+props.id}
                initialValue={props.notes || "(no notes)"}
                labelClass={"picture-notes-label w-100"}
                inputClass={"picture-notes-input w-100"}
                save={(txt) => props.handleChangeProductNote(props.id, txt)}
                canEdit={props.canEdit}
            />
            <div className={"d-flex flex-wrap w-100 justify-content-between"}>
                <span className={"portfolio-box-title"}><b>Products in picture</b></span>
                {Icons.Add(() => props.setVisibleProductSearch(true), "Add")}
            </div>
            <PictureProductsList
                picture_id={props.id}
                picture_notes={props.notes}
                products_assoc={props.products_assoc}
                products_assoc_id={props.products_assoc_id}
                onProductPictureDelete={(product_id) => props.handleDeleteProductPicture(props.id, product_id)}
            />
        </div>
    )
}

const PictureProductsList = (props) => {
    if(props.products_assoc.length == 0){
        return (
            <>
            </>
        );
    }
    return (
        <div className={"d-flex flex-wrap my-2"}>
            {props.products_assoc_id.map((product_id, ix) => (
                <span key={[props.picture_id, product_id].join('-')} className='card-product-name'>
                    {props.products_assoc[ix]}
                    {Icons.Close(() => {
                        show_swal({},
                            {
                                title: `Are you sure you want to delete ${props.products_assoc[ix]} from ${props.picture_notes}`
                            },
                            {
                                complete: () => props.onProductPictureDelete(product_id)
                            }
                        );
                    })}
                </span>
            ))}
        </div>
    );
}