import React, {useEffect, useState, useReducer} from 'react';
import ReactDOM from 'react-dom';
import { AsyncTypeahead } from 'react-bootstrap-typeahead';

import { getProductSearch } from "../services/portfolioService";

export const AsyncTypeaheadInput = (props) => {
    const [options, setOptions] = useState([]);
    const [selected, setSelected] = useState([]);
    const [isLoading, setLoading] = useState(false);
    // const [query, setQuery] = useState("");

    const PER_PAGE = 50;

    const handleSearch = (query) => {
        console.log("Search:", query);
        setLoading(true);
        getProductSearch({
            values: {query: query},
            successCallback: (result) => {
                setOptions(result);
            }
        });
        setLoading(false);
    }

    const handleSelect = (selectedOption) => {
        if(selectedOption.length > 0){
            console.log(selectedOption[0]);
            const item_id = selectedOption[0].id.split('-')[0];
            const full_name = selectedOption[0].label.split(' / ')
            const product_name = [full_name[0], full_name[full_name.length-1]].join('-');
            props.onSelect({name:product_name, id:item_id});
            setSelected([]);
        }
    }

    return (
        <AsyncTypeahead
            className={(props.hide ? "hide" : "")}
            isLoading={isLoading}
            options={options}
            // query={query}
            selected={selected}
            id="async-pagination-example"
            labelKey="label"
            maxResults={PER_PAGE - 1}
            minLength={2}
            // onInputChange={handleInputChange}
            // onPaginate={handlePagination}
            onSearch={handleSearch}
            onChange={handleSelect}
            // paginate
            clearButton={true}
            placeholder={props.placeholder}
            useCache={false}
        />
    )
}