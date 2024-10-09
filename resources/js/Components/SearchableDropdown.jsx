import { useState } from "react";
import axios from 'axios';

// TODO: test

const SearchableDropdown = (props) => {
    const [results, setResults] = useState([]);
    const [isSearching, setIsSearching] = useState(false);
    const [isLoading, setIsLoading] = useState(false);

    const setIsSearchingWhenHasQuery = (query) => {
        setIsSearching(query.length !== 0);
    }

    const querySource = (query) => {
        setIsSearchingWhenHasQuery(query);
        setIsLoading(true);

        if (query.length === 0) return;

        // FIXME: handle failure case
        axios.get(props.sourceRoute + "?query=" + query).then((response) => {
            setResults(response.data.data);
            setIsLoading(false);
        });
    }

    const LoadingDropdownContent = () => {
        return <li>Loading...</li>
    }

    const NoResultsDropdownContent = () => {
        return <li>No results found</li>
    }

    const ResultsDropdownContent = () => {
        return results.map((result) =>
            <li>{result.name}</li>
        );
    }

    const DropdownContent = () => {
        if (isLoading) return <LoadingDropdownContent />
        if (results.length === 0) return <NoResultsDropdownContent />
        return <ResultsDropdownContent />
    }

    return (
        <div className="position-relative w-100" onBlur={() => setIsSearching(false)}>
            <input
                type="text"
                placeholder="Search..."
                className="w-100"
                name="department_name"
                id="department_name"
                onClick={(event) => setIsSearchingWhenHasQuery(event.target.value)}
                onChange={(event) => querySource(event.target.value)}
                required
            />
            {isSearching && (
                <div className="w-100 border shadow bg-light position-absolute" style={{maxHeight: "200px", overflowY: "auto", zIndex: "1"}}>
                    <ul className="list-unstyled m-2">
                        <DropdownContent />
                    </ul>
                </div>
            )}
        </div>
    );
}

export default SearchableDropdown;
