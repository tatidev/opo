import React from 'react';

export const Icons = {
    Active: (onClick) => <i className="fal fa-toggle-on" onClick={onClick}></i>,
    NonActive: (onClick) => <i className="fal fa-toggle-off" onClick={onClick}></i>,
    Status: (isActive, onClick) => {
        if(isActive){
            return Icons.Active(onClick);
        }
        return Icons.NonActive(onClick);
    },
    Add: (onClick, text="") => (<span onClick={onClick}>{text} <i className="fal fa-plus"></i></span>),
    Trash: (onClick) => <i className="fas fa-trash" onClick={onClick}></i>,
    Close: (onClick) => <i className="fas fa-window-close" onClick={onClick}></i>,
    ProjectNotes: () => (<i className="fas fa-question-circle" data-class={"tip-project-notes"} data-tip="This box contains the origin of the images (Instagram, designer’s website, hotel website, etc.), the photographer’s name and Instagram account (if available) and any other meaningful information about the usage of the photo." data-placement="top" data-original-title="" title=""></i>)
}