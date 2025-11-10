import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';
import {useFormik} from "formik";
import * as yup from 'yup';

export const ProjectForm = (props) => {
    const initialValues = {
        name: '',
        notes: ''
    }

    let validationSchema = yup.object().shape({
        name: yup.string().required("Required")
    })

    const formik = useFormik({
        initialValues: initialValues,
        validationSchema,
        onSubmit: (values) => {
            validationSchema
                .isValid(values)
                .then((valid) => {
                    if(valid){
                        props.handleSubmit(values, () => clearForm());
                    }
                })
                .catch(function (err) {
                    show_alert(err.errors);
                });

        }
    });

    const clearForm = () => {
        formik.setSubmitting(false);
        formik.resetForm({values: initialValues});
        // document.getElementById("files").value = "";
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
                    {formik.errors.name ? formik.errors.name : null}
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
                    {! formik.isSubmitting ?
                        <button type={"submit"} className={"btn btn-success float-right"} /*disabled={formik.isSubmitting}*/ >
                            Save
                        </button>
                    :
                        <button className={"btn float-right"} /*disabled={formik.isSubmitting}*/ >
                            Loading
                        </button>
                    }

                </div>
            </div>
        </form>
    )

}
