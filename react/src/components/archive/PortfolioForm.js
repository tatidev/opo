import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';


export const PortfolioForm = (props) => {
    const [notes, setNotes] = useState("");
    // const [selectedFile, setSelectedFile] = useState("");

    const handleSubmit = () => {
        const formData = new FormData();
        const filesList = $('input[type="file"]').prop('files');
        if(notes.length == 0){
            show_alert("Notes are required.")
            return;
        }
        if(filesList.length == 0){
            show_alert("File is required.")
            return;
        }
        formData.append('files', filesList[0]); // Force 1 single file
        formData.append('notes', notes);
        props.handleSubmit(formData, () => {
            setNotes('');
            document.getElementById("new_file").value = "";
        });
    }

    return (
        <form id={props.formID} encType="multipart/form-data">
            <div className={"row"}>
                <div className={"col-6"}>
                    <div className={"form-group row"}>
                        <label htmlFor={"notes"} className={"col-xs-12 col-sm-3 col-form-label"}>Picture notes</label>
                        <div className={"col-xs-12 col-sm-9"}>
                            <input
                                id={"new_notes"}
                                className={"w-100"}
                                name={"new_notes"}
                                type={"text"}
                                value={notes}
                                onChange={(e) => {
                                    // console.log(e.target.value);
                                    setNotes(e.target.value);
                                }}
                            />
                        </div>
                    </div>
                </div>
                <div className={"col-6"}>
                    <div className={"form-group row"}>
                        <div className={"col-xs-12 col-sm-9"}>
                            <input
                                id={"new_file"}
                                name={"new_file"}
                                type={"file"}
                            />
                        </div>
                    </div>
                </div>
                <div className={"col-12"}>
                    <div className={"form-group row"}>
                        <div className={"col"}>
                            <button type={"button"} className={"btn btn-success float-right"} onClick={handleSubmit}>
                                Save
                            </button>
                            <input type="reset" style={{display: "none"}} />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    )
}