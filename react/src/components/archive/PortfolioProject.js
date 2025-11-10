import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';
import Carousel from 'react-bootstrap/Carousel'
import {BaseURL, makeRequest} from "../services/base";

const Icons = {
    Active: (onClick) => <i className="fal fa-toggle-on" onClick={onClick}></i>,
    NonActive: (onClick) => <i className="fal fa-toggle-off" onClick={onClick}></i>,
    Status: (isActive, onClick) => {
        if(isActive){
            return Icons.Active(onClick);
        }
        return Icons.NonActive(onClick);
    },
    Add: (onClick) => <i className="fal fa-plus" onClick={onClick}></i>,
    Trash: (onClick) => <i className="fas fa-trash" onClick={onClick}></i>,
    Close: (onClick) => <i className="fas fa-window-close" onClick={onClick}></i>
}

export const PortfolioProject = (props) => {
    const isActive = props.data.active == '1';
    const [isLoading, setIsLoading] = useState(true);
    const [data, setData] = useState({result: [], totalCount: -1});
    const [currImgIndex, setImgIndex] = useState(0);

    const fetchData = async () => {
        setIsLoading(true);
        makeRequest({
            url: 'portfolio/get_pictures',
            requestType: 'GET',
            values: {
                project_id: props.data.id
            },
            successCallback: (data) => {
                console.log("Project", props.data, data);
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
    }, []);

    const handleCarouselSelect = (selectedIndex, e) => {
        setImgIndex(selectedIndex);
    }


    if(isLoading){
        return (
            <>
                Loading Project {props.data.id}
            </>
        )
    }
    return (
        <div key={props.data.id} className="card">
            <Carousel activeIndex={currImgIndex} onSelect={handleCarouselSelect} interval={null}>
                {
                    data.result.length > 0 ?
                        data.result.map((row, ix) => {
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
                                onClick={() => console.log("Upload new")}
                            />
                        </Carousel.Item>
                }
            </Carousel>
            <div className="card-body">
                <p className="card-text">
                    {
                        data.result.length == 0 ?
                            <>
                            </>
                            :
                            data.result[currImgIndex].notes
                    }
                </p>
                <p className="card-text">
                    {
                        data.result.length == 0 ?
                            <>
                            </>
                            :
                            <PictureProductsList
                                picture_id={data.result[currImgIndex].id}
                                picture_notes={data.result[currImgIndex].notes}
                                products_assoc={data.result[currImgIndex].products_assoc}
                                products_assoc_id={data.result[currImgIndex].products_assoc_id}
                                onProductPictureAdd={(product_id) => console.log("Add Product")}
                                onProductPictureDelete={(product_id) => console.log("Handle ProductPicture delete, Product", product_id, "Project", props.data.id)}
                                />
                    }

                </p>
                <p className="card-text">
                    Project info<br />
                    ID {props.data.id} <br />
                    {props.data.name} <br />
                    {props.data.notes} <br />
                </p>
                <div className="d-flex justify-content-between">
                    {Icons.Status(isActive, () => console.log("Status Project", props.data.id))}
                    {Icons.Add(() => console.log("Add photo to project", props.data.id))}
                    {Icons.Trash(() => {
                        show_swal(
                            {},
                            {
                                title: `Are you sure you want to delete the project ${props.data.name}?`
                            },
                            {
                                complete: () => console.log("Delete project", props.data.id)
                            }
                        );
                    })}
                </div>
            </div>
        </div>
    )
}

export const PictureProductLabel = (props) => {
    return (
        <span className='card-product-name'>
            {props.product_name}
            {Icons.Close(() => {
                show_swal({},
                    {
                        title: `Are you sure you want to delete ${props.product_name} from ${props.picture_notes}`
                    },
                    {
                        complete: () => props.onProductPictureDelete()
                    }
                );
            })}
        </span>
    )
}

export const PictureProductsList = (props) => {
    if(props.products_assoc.length == 0){
        return (
            <></>
        );
    }
    // console.log(props);
    return (
        <>
            {props.products_assoc_id.map((product_id, ix) => (
                <PictureProductLabel
                    key={[props.picture_id, product_id].join('-')}
                    picture_id={props.picture_id}
                    product_id={product_id}
                    product_name={props.products_assoc[ix]}
                    picture_notes={props.picture_notes}
                    onProductPictureDelete={() => props.onProductPictureDelete(product_id)}
                />
            ))}
        </>
    );
}