import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';
import {makeRequest} from "../../services/base";

import Modal from 'react-bootstrap/Modal'
import Button from 'react-bootstrap/Button'
import ProgressBar from 'react-bootstrap/ProgressBar'
import '../../css/style.css';
import '../../css/style_sourcebook.css';
var _ = require('lodash');

const LOGO_BLACK_ON_WHITE_URL = "https://app.opuzen.com/pms/assets/images/opuzen_blackonwhite_272.png";

const Modal_Body_Columns = [
    // {path: 'product_name', label: 'Product', visible: true},
    // {path: 'color', label: 'Color', visible: true},
    // {path: 'code', label: 'Code', visible: true},
    {path: 'status', label: 'Status', visible: true},
    {path: 'width', label: 'Width', visible: true},
    {path: 'repeats', label: 'Repeats', visible: true},
    {path: 'uses', label: 'Uses', visible: true},
    {path: 'weave', label: 'Weave', visible: true},
    {path: 'abrasion', label: 'Abrasion', visible: true},
    {path: 'firecode', label: 'Firecode', visible: true},
    {path: 'cleaning', label: 'Cleaning', visible: true},
    {path: 'content_front', label: 'Content', visible: true},
    {path: 'content_back', label: 'Content Back', visible: true},
    {path: 'finish', label: 'Finish', visible: true},
];

export const SourceBook = (props) => {
    const [isLoading, setIsLoading] = useState(true);
    const [data, setData] = useState([]);
    const [cardModalID, setCardModalID] = useState(0);
    const [cardModalData, setCardModalData] = useState([]);

    const fetchData = async () => {
        setIsLoading(true);
        makeRequest({
            url: 'lists/get_sourcebook',
            requestType: 'GET',
            successCallback: (r) => {
                // console.log("Project", props.id, r);
                setData(r.data);
                setIsLoading(false);
            },
            failureCallback: (e) => {
                console.log(e);
                setIsLoading(false);
            }
        });
    }

    const fetchCardData = async () => {
        if(cardModalID == 0) {
            return;
        };
        setIsLoading(true);
        makeRequest({
            url: 'reps/item/get/'+cardModalID,
            requestType: 'GET',
            successCallback: (r) => {
                console.log("item/get", r);
                setCardModalData(r.data);
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

    useEffect(() => {
        fetchCardData();
    }, [cardModalID]);

    if(data.length == 0){
        return (
            <div className={"w-100"}>
                <ProgressBar animated now={100} />
                <div className={"w-100 text-center"}>
                    Loading folder...<br />
                    Please wait, this could take a minute to load all images.
                </div>
            </div>
        )
    }
    else {
        return (
            <>
                {SourceBookRender({data: data, handleCardClick:(id)=>setCardModalID(id)})}
                {SourceBookCardModal({id: cardModalID, ...cardModalData, handleClose:()=>setCardModalID(0)})}
            </>
        );
    }
}

const SourceBookRender = (props) => {
    const N_ROWS_PER_PAGE = 6;
    const N_COLS = 4;
    const CARDS_PER_PAGE = N_ROWS_PER_PAGE * N_COLS;

    const data = props.data;
    let html = [];
    var current_page = 1;
    var total_items = 0;
    var total_pages = 0;
    let list_length = 0;
    for(var k in data){
        list_length = data[k].length;
        total_items += list_length;
        total_pages += Math.ceil(list_length / CARDS_PER_PAGE);
    }

    for(let list_key in data){
        let pages_for_list = Math.ceil(data[list_key].length / CARDS_PER_PAGE);

        // Get the render for each page
        // Slice data for each page and get SourceBookPage
        for(let i = 0; i < pages_for_list; i++){
            let blank_cards_to_fill = [];
            let data_offset_from = i*CARDS_PER_PAGE;
            let data_offset_to = Math.min(data_offset_from + CARDS_PER_PAGE, data[list_key].length);

            // We might need to add some additional blanks to fill page
            if(data_offset_to == data[list_key].length){
                const to_add_cards = CARDS_PER_PAGE - (props.data.length + 1);
                for(let h = 0; h <= to_add_cards; h++){
                    blank_cards_to_fill.push(null);
                }
            }

            html.push(
                SourceBookPage({
                    title: i == 0 ? list_key : null,
                    N_ROWS_PER_PAGE: N_ROWS_PER_PAGE,
                    N_COLS: N_COLS,
                    CARDS_PER_PAGE: CARDS_PER_PAGE,
                    data: [...data[list_key].slice(data_offset_from, data_offset_to), ...blank_cards_to_fill],
                    current_page: current_page,
                    total_pages: total_pages,
                    handleCardClick: props.handleCardClick
                })
            )
            current_page++;
        }
    }

    return (
        <div className={"book-wrap"}>
            {html}
        </div>
    );
}

const SourceBookPageFooter = (props) => {
    return (
        <div className={"page-footer"}>
            <div className={"page-number"}>{props.page}/{props.total_pages}</div>
        </div>

    )
}

const SourceBookPage = (props) => {
    let content = [];
    const N_ROWS_PER_PAGE = props.N_ROWS_PER_PAGE;
    const N_COLS = props.N_COLS;
    const CARDS_PER_PAGE = props.CARDS_PER_PAGE;

    for(let i = 0; i < props.N_ROWS_PER_PAGE; i++){
        let data_offset_from = i * N_COLS;
        let data_offset_to = Math.min(data_offset_from + N_COLS, props.data.length);
        content.push(
            SourceBookPageRow({
                key: (props.current_page*N_ROWS_PER_PAGE) + i,
                N_COLS: N_COLS,
                data: props.data.slice(data_offset_from, data_offset_to),
                handleCardClick: props.handleCardClick
            })
        );
    }

    return (
        <div key={"Page-"+String(props.current_page)} className={"sourcebook-page"}>
            {SourceBookPageHeader({title: props.title})}
            {content}
            {SourceBookPageFooter({page: props.current_page, total_pages: props.total_pages})}
        </div>
    )
}

const SourceBookPageRow = (props) => {
    let N_COLS = props.N_COLS;
    let content = props.data.map(d => SourceBookCard({...d, handleCardClick: props.handleCardClick}));
    let cols_to_add = N_COLS - props.data.length;

    // if(cols_to_add > 0){
    //     console.log("Row", props.key, "got", props.data.length, "filling with", cols_to_add);
    // }

    for(let i = 0; i < cols_to_add; i++){
        content.push(SourceBookCard(null));
    }
    return (
        <div key={"Row-"+String(props.key)} className={"sourcebook-row d-flex justify-content-around"}>
            {content}
        </div>
    )
}

const SourceBookPageHeader = (props) => {
    if (props.title == null){
        return '';
    }
    return (
        <div className={"sourcebook-page-header"}>
            <div className={"sourcebook-page-header-logo"}>
                <img src={LOGO_BLACK_ON_WHITE_URL} />
            </div>
            {SourceBookCatalogueTitle(props)}
        </div>
    )
}

const SourceBookCatalogueTitle = (props) => {
    let title = props.title.split('-');
    // title = title[1].trim().replace('/', '  ');
    title = title[1].split('/');

    return (
        <div key={"TITLE"+title} className={"catalogue-title"}>
            FAUX LEATHER SOURCE BOOK
            <br />
            {title[0]} &bull; {title[1]}
        </div>
    )
}

const SourceBookCard = (props) => {
    if(props == null){
        const rng_id = Math.ceil(Math.random()*10000);
        return (
            <div key={"Card-"+String(rng_id)} className={"sourcebookcard"}>
            </div>
        )
    }
    return (
        <div key={"Card-"+String(props.item_id)} className={"sourcebookcard"}>
            <div className={"d-flex flex-column"}>
                <div className={"sourcebookcard-img-wrap"} onClick={()=>props.handleCardClick(props.item_id)}>
                    <img className={"sourcebookcard-img"} src={props.pic_url} />
                </div>
                {SourceBookCardText(props)}
            </div>
        </div>
    )
}

const SourceBookCardText = (props) => {
    const txt_main = (txt) => <div className={"sourcebookcard-code"}>{txt}</div>
    const txt_normal = (txt, cls='') => <div className={cls}>{txt}</div>

    const graffiti_free_text = txt_normal("Graffiti Free", "text-right")
    const ltdqty_text = txt_normal("Limited Quantity", "text-right")
    const mso_text = txt_normal("Mill Special Order", "text-right")

    return (

        <div className={"row"}>
            <div className={"col-5"}>
                <div className={"sourcebookcard-text-wrap d-flex flex-column"}>
                    {txt_main(props.v_code)}
                    {txt_normal(props.content)}
                </div>
            </div>
            <div className={"col"}>
                <div className={"sourcebookcard-text-wrap d-flex flex-column float-right"}>
                    {(props.graffiti_free == 1 ? graffiti_free_text : "")}
                    {(props.status == 'LTDQTY' ? ltdqty_text : "")}
                    {(props.status == 'MSO' ? mso_text : "")}
                </div>
            </div>
        </div>
    )
}

const SourceBookCardModal = (props) => {
    if(props.id == 0){
        return "";
    }
    // console.log(props);
    return (
        <Modal show={'item_id' in props} onHide={props.handleClose} dialogClassName="modal-75w" fullscreen="sm-down">
            <SourceBookCardModalBody {...props} />
            <Modal.Footer>
                <Button variant="secondary" onClick={props.handleClose}>
                    Close
                </Button>
            </Modal.Footer>
        </Modal>
    )
}

const SourceBookCardModalBody = (props) => {
    var html_data = [];
    for(var ix in Modal_Body_Columns){
        const value = _.get(props, Modal_Body_Columns[ix].path)
        const label = Modal_Body_Columns[ix].label;
        // console.log("Label:", label, "Value:", value, typeof(value))
        if(Modal_Body_Columns[ix].visible && value !== null && value.length > 0){
            const col_data = <><dt className="col-4">{label}:</dt><dd className="col-8">{value}</dd></>
            html_data.push(col_data)
        }
    }
    return (
        <Modal.Body>
            <div className="row">
                <div className="col-6">
                    <dl className={"row"}>
                        <dt className="col-4">Product:</dt><dd className="col-8">{props.product_name}</dd>
                        <dt className="col-4">Color:</dt><dd className="col-8">{props.color}</dd>
                        <dt className="col-4">Code:</dt><dd className="col-8">{props.code}</dd>
                    </dl>
                    <dl className={"row"}>
                        {html_data}
                    </dl>
                </div>
                <div className={"col-6 d-flex flex-column"}>
                    <div className={"d-flex flex-row"}>
                        <a className={"oz-color oz-btn"} href={props.spec_url} target="_blank">DOWNLOAD SPECSHEET</a>
                    </div>
                    <div className="d-flex flex-column text-center">
                        <img className={"my-4"} width={"400px"} src={props.pic_big_url} />
                    </div>
                </div>
            </div>
        </Modal.Body>
    )
}