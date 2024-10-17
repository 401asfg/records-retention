import { useState, useEffect } from "react";
import axios from 'axios';
import { useCookies } from 'react-cookie';

// TODO: test
// TODO: test cookie system
// TODO: add clear button?
// TODO: take burden off server by querying all once on creation, then filtering results on client side for each query?

const SearchableDropdown = (props) => {
    const COOKIE_NAME = props.name + "_query";

    const [cookies, setCookie] = useCookies();

    const [query, setQuery] = useState(props.selectedOptionId === null ? "" : (cookies[COOKIE_NAME] || ""));
    const [results, setResults] = useState([]);
    const [isDropdownOpen, setDropdownOpen] = useState(false);
    const [isLoading, setLoading] = useState(false);

    useEffect(() => {
        if (query.length === 0) return;
        setLoading(true);

        // FIXME: handle failure case
        axios.get(props.sourceRoute + "?query=" + query)
            .then((res) => { setResults(res.data.data); })
            .finally(() => { setLoading(false); });
    }, [query]);

    const selectResult = (index) => {
        if (index < 0 || index >= results.length) return;

        const result = results[index];
        const id = result.id;
        const name = result.name;

        props.setSelectedOptionId(id);
        setQuery(name);
        setCookie(COOKIE_NAME, name, props.cookieOptions);
    }

    const setDropdownToOpenOnValidQuery = (query) => {
        setDropdownOpen(query.length !== 0);
    }

    const endSearch = () => {
        setDropdownOpen(false);
        if (props.selectedOptionId === null) setQuery("");
    }

    const search = async (query) => {
        props.setSelectedOptionId(null);
        setDropdownToOpenOnValidQuery(query);
        setQuery(query);
    }

    const LoadingDropdownContent = () => {
        return <li className="p-2">Loading...</li>
    }

    const NoResultsDropdownContent = () => {
        return <li className="p-2">No results found</li>
    }

    const ResultsDropdownContent = () => {
        const Result = (props) => {
            const [isHovered, setIsHovered] = useState(false);

            return (
                <li
                    onMouseEnter={() => setIsHovered(true)}
                    onMouseLeave={() => setIsHovered(false)}
                    onMouseDown={() => selectResult(props.index)}
                    className="p-2"
                    style={{backgroundColor: isHovered ? "#00BFFF" : ""}}
                >{props.result.name}</li>
            );
        }

        return results.map((result, i) => {
            return <Result key={result.id} result={result} index={i} />
        });
    }

    const DropdownContent = () => {
        if (isLoading) return <LoadingDropdownContent />
        if (results.length === 0) return <NoResultsDropdownContent />
        return <ResultsDropdownContent />
    }

    return (
        <div className="position-relative w-100">
            <input
                type="text"
                placeholder="Search..."
                className="w-100"
                value={query}
                onClick={(event) => setDropdownToOpenOnValidQuery(event.target.value)}
                onBlur={endSearch}
                onChange={(event) => search(event.target.value)}
                required
            />
            {isDropdownOpen && (
                <div className="w-100 border shadow bg-light position-absolute" style={{maxHeight: "200px", overflowY: "auto", zIndex: "1"}}>
                    <ul className="list-unstyled mb-0">
                        <DropdownContent />
                    </ul>
                </div>
            )}
        </div>
    );
}

export default SearchableDropdown;
