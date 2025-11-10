import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';
import {useFormik} from "formik";
import * as yup from 'yup';

export const PortfolioProjectForm = (props) => {
    const initialValues = {
        name: '',
        notes: ''
    }

    let schema = yup.object().shape({
        name: yup.string().required()
    })

    const formik = useFormik({
        initialValues: initialValues,
        onSubmit: (values) => {
            schema
                .isValid(values)
                .then((valid) => {
                    const formData = new FormData();
                    const filesList = $('input[type="file"]').prop('files');
                    formData.append('name', values.name);
                    formData.append('notes', values.notes);
                    props.handleSubmit(formData, () => clearForm());
                })
                .catch(function (err) {
                    show_alert(err.errors);
                });

        }
    });

    const clearForm = () => {
        formik.setSubmitting(false);
        formik.resetForm({values: initialValues});
        document.getElementById("files").value = "";
        show_alert(false);
    }
    // console.log(formik);

    return (
        <form onSubmit={formik.handleSubmit}>
            <div className={"row"}>
                <div className={"col-6"}>
                    <div className={"form-group row"}>
                        <label htmlFor={"name"} className={"col-xs-12 col-sm-3 col-form-label"}>Project name
                        </label>
                        <div className={"col-xs-12 col-sm-9"}>
                            <input
                                id={"name"}
                                className={"w-100"}
                                name={"name"}
                                type={"text"}
                                onChange={formik.handleChange}
                                value={formik.values.name}
                            />
                        </div>
                    </div>
                </div>
                <div className={"col-6"}>
                </div>
                <div className={"col-6"}>
                    <div className={"form-group row"}>
                        <label htmlFor={"notes"} className={"col-xs-12 col-sm-3 col-form-label"}>Notes</label>
                        <div className={"col-xs-12 col-sm-9"}>
                            <input
                                id={"notes"}
                                className={"w-100"}
                                name={"notes"}
                                type={"text"}
                                onChange={formik.handleChange}
                                value={formik.values.notes}
                            />
                        </div>
                    </div>
                </div>
                <div className={"col-6"}>
                </div>
                <div className={"col-12"}>
                    <button type={"submit"} className={"btn btn-success float-right"} disabled={formik.isSubmitting}>
                        {formik.isSubmitting ? "Loading..." : "Save"}
                    </button>
                </div>
            </div>
        </form>
    )

}
