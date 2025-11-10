import React from 'react';
import ReactDOM from 'react-dom';

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
            <>
            </>
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

export const ProjectGallery = (props) => {
    const data = props.data;
    const isActive = data.active == '1';

    return (
        <div className="card">
            <div className="card-img-container">
                <i className="fad fa-do-not-enter inactive-icon " style={!isActive ? {opacity:1}: {opacity:0}}></i>
                <a href={data.url} target={"_blank"}>
                    <img src={data.url} className="card-img-top" style={!isActive ? {opacity:0.5} : {}} />
                </a>
            </div>
            <div className="card-body">
                <p className="card-text">{data.notes}</p>
                <p className="card-text">
                    <PictureProductsList
                        picture_id={data.id}
                        picture_notes={data.notes}
                        products_assoc={data.products_assoc}
                        products_assoc_id={data.products_assoc_id}
                        onProductPictureDelete={(product_id) => props.onProductPictureDelete(product_id, data.id)}/>
                </p>
                <div className="d-flex justify-content-between">
                    {Icons.Status(isActive, () => props.onPictureStatusToggle(data.id))}
                    {Icons.Add(() => props.onProductPictureAdd(data.id))}
                    {Icons.Trash(() => {
                        show_swal(
                            {},
                            {
                                title: `Are you sure you want to delete the picture for ${data.notes}?`
                            },
                            {
                                complete: () => props.onPictureDelete(data.id)
                            }
                        );
                    })}
                </div>
            </div>
        </div>
    );
}