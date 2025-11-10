import React from 'react';
import Pagination from 'react-bootstrap/Pagination';
import { range } from "../utils/list";

export const MY_Pagination = (props) => {
    const { totalCount, size_per_page, page, onPageChange, class: className } = props;

    const number_of_pages = Math.ceil(totalCount / size_per_page);
    const window_size = 10;

    if (totalCount <= 0) {
        return <Pagination className={className} />;
    }

    const startPage = Math.max(1, page + 1 - Math.floor(window_size / 2));
    const endPage = Math.min(number_of_pages, startPage + window_size - 1);
    const visiblePages = range(startPage, endPage + 1);

    const showLeftEllipsis = startPage > 1;
    const showRightEllipsis = endPage < number_of_pages;

    return (
        <Pagination className={className}>
            <Pagination.First onClick={() => onPageChange(0)} disabled={page === 0} />
            <Pagination.Prev onClick={() => onPageChange(page - 1)} disabled={page === 0} />

            {showLeftEllipsis && (
                <>
                    <Pagination.Item onClick={() => onPageChange(0)}>{1}</Pagination.Item>
                    <Pagination.Ellipsis disabled />
                </>
            )}

            {visiblePages.map((value) => (
                <Pagination.Item
                    key={value}
                    active={page + 1 === value}
                    onClick={() => onPageChange(value - 1)}
                >
                    {value}
                </Pagination.Item>
            ))}

            {showRightEllipsis && (
                <>
                    <Pagination.Ellipsis disabled />
                    <Pagination.Item onClick={() => onPageChange(number_of_pages - 1)}>
                        {number_of_pages}
                    </Pagination.Item>
                </>
            )}

            <Pagination.Next onClick={() => onPageChange(page + 1)} disabled={page + 1 === number_of_pages} />
            <Pagination.Last onClick={() => onPageChange(number_of_pages - 1)} disabled={page + 1 === number_of_pages} />
        </Pagination>
    );
};
