const SourceBookRender2 = (props) => {
    const N_ROWS_PER_PAGE = 6;
    const N_COLS = 4;
    const data = props.data;
    let html = [];
    var current_page = 1;
    var total_items = 0;
    for(var k in data){
        total_items += data[k].length;
    }
    const total_pages = Math.ceil(total_items / (N_ROWS_PER_PAGE*N_COLS));

    for(let list_key in data){
        // Enter pages for this list
        const title = SourceBookCatalogueTitle({title: list_key});
        html.push(title);
        let catalogue_data = [];
        [catalogue_data, current_page] = SourceBookCatalogue({
            title: list_key,
            data: data[list_key],
            current_page: current_page,
            total_pages: total_pages,
            N_ROWS_PER_PAGE: N_ROWS_PER_PAGE,
            N_COLS: N_COLS
        });
        html.push(catalogue_data);
        html.push(FooterPageNumber({page: current_page, total_pages: total_pages}));
        html.push(PrintPageBreak({key: "PB-"+list_key}));
        current_page++;
    }

    return (
        <div className={"book-wrap"}>
            {html}
        </div>
    );
}

const SourceBookCatalogue = (props) => {
    const data = props.data;
    const N_ROWS_PER_PAGE = props.N_ROWS_PER_PAGE;
    const N_COLS = props.N_COLS;
    const N_ROWS = Math.ceil(data.length / N_COLS);
    let html = [];
    let current_page = props.current_page;
    let i = 0;
    const title = SourceBookCatalogueTitle({title: props.title});

    while(i < N_ROWS){
        let j = 0;
        if(i > 0 && i % N_ROWS_PER_PAGE == 0){
            html.push(FooterPageNumber({page: current_page, total_pages: props.total_pages}));
            html.push(PrintPageBreak({key: "PB"+String(i*N_COLS + j)}));
            html.push(title);
            current_page++;
        }
        while(j < N_COLS){
            let ix = i*N_COLS + j;
            if(ix == data.length){
                // Add some additinal empty SourceBookCard so that they align to the left properly
                const to_add = (data.length+1) % N_COLS;
                let h = 0;
                while(h <= to_add){
                    html.push(SourceBookCard(null));
                    h++;
                }
                break;
            };
            html.push(SourceBookCard(data[ix]));
            j++;
        }
        html.push(RowBreak({key: "FB"+String(i*N_COLS + j)}));
        i++;
    }


    html = (
        <>
            <div key={"WP"+title} className={"catalogue-wrap"}>
                {html}
            </div>
        </>
    );
    return [html, current_page];
}

const FooterPageNumber = (props) => {
    return (
        <div className={"page-number"}>{props.page}/{props.total_pages}</div>
        // <div className={"page-number"}></div>
    )
}

const PrintPageBreak = (props) => {
    return (
        <div key={props.key} className={"page-break-after"}></div>
    )
}

const RowBreak = (props) => {
    return (
        <div key={props.key} className={"flex-break"}></div>
    )
}