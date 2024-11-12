import SearchableDropdown from './SearchableDropdown';
import '@testing-library/jest-dom';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import React, { useState } from 'react';

describe(SearchableDropdown, () => {
    function SearchableDropdownMock(props) {
        const [departmentId, setDepartmentId] = useState(props.initDepartmentId);
        return <SearchableDropdown
            name="test"
            sourceRoute="api/departments"
            selectedOptionId={departmentId}
            setSelectedOptionId={setDepartmentId}
            cookieOptions={{ maxAge: 1209600 }}
        />
    }

    function renderSearchableDropdown(initDepartmentId) {
        render(<SearchableDropdownMock initDepartmentId={initDepartmentId} />);
    }

    describe("Initialization", () => {
        function expectSearchableDropdown(input) {
            waitFor(() => {
                expect(screen.getByTestId("searchable-dropdown-input")).toHaveValue(input);
                expect(screen.queryByTestId("searchable-dropdown-list")).toBeNull();
            });
        }

        it("should have an empty input and no dropdown if given a null initial selection option id", () => {
            renderSearchableDropdown(null);
            expectSearchableDropdown("");
        })

        it("should contain the name of the selection option that corresponds to a valid initial selection option id in the input and no dropdown", () => {
            renderSearchableDropdown(0);
            expectSearchableDropdown("Engineering Labs");
        })

        it("should have an empty input and no dropdown if the given initial selection option id does not correspond to a valid selection option", () => {
            renderSearchableDropdown(9999);
            expectSearchableDropdown("");
        })

        it("should have an empty input and no dropdown if the given initial selection option id is not an integer", () => {
            renderSearchableDropdown("not an integer");
            expectSearchableDropdown("");
        })

        // TODO: submission testing needs to check what happens if the initial id is invalid or out of range
    })

    describe("Initial Typing States", () => {
        function clickOn() {
            fireEvent.click(screen.getByTestId("searchable-dropdown-input"));
        }

        function clickOff() {
            fireEvent.click(document);
        }

        function type(input) {
            fireEvent.change(screen.getByTestId("searchable-dropdown-input"), { target: { value: input } });
        }

        function expectInput(input) {
            expect(screen.getByTestId("searchable-dropdown-input")).toHaveValue(input);
        }

        function expectNoDropdown(input) {
            waitFor(() => {
                expectInput(input);
                expect(screen.queryByTestId("searchable-dropdown-list")).toBeNull();
            });
        }

        function expectSearchableDropdown(input, dropdownType) {
            waitFor(() => {
                expectInput(input);
                expect(screen.queryByTestId("searchable-dropdown-" + dropdownType)).not.toBeNull();
            });
        }

        function expectLoading(input) {
            expectSearchableDropdown(input, "loading");
        }

        function expectNoResults(input) {
            expectSearchableDropdown(input, "no-results");
        }

        function expectResults(input, results) {
            waitFor(() => {
                expectInput(input);

                results.forEach((result, i) => {
                    expect(screen.getByTestId("searchable-dropdown-result-" + i)).toHaveTextContent(result);
                })
            })
        }

        // TODO: test when clicking off at different points

        beforeEach(() => {
            renderSearchableDropdown(null);
        })

        it("should not display the dropdown when the input is initially clicked", () => {
            clickOn();
            expectNoDropdown("");
        })

        it('should clear the input and dropdown when clicked off right after clicking on', () => {
            clickOn();
            clickOff();
            expectNoDropdown("");
        })

        it('should display all the results that contain the first character entered', () => {
            type("s");
            expectLoading("s");
            expectResults("s", [
                'Engineering Labs',
                'Medical Testing',
                'Admin Offices',
                'Shipping and Receiving',
                'Shipping or Receiving',
                'History'
            ]);
        })

        it('should clear the input and dropdown when clicked off after typing one character', () => {
            type("s");
            clickOff();
            expectNoDropdown("");
        })

        it('should display a results empty message when no results contain the first character entered', () => {
            type("z");
            expectLoading("z");
            expectNoResults("z");
        })

        it('should display all the results that contain the character sequence entered', () => {
            type("ship");
            expectLoading("ship");
            expectResults("ship", [
                'Shipping and Receiving',
                'Shipping or Receiving'
            ]);
        })

        it('should clear the input and dropdown when clicked off after typing a valid character sequence', () => {
            type("ship");
            clickOff();
            expectNoDropdown("");
        })

        it('should display a results empty message when no results contain the character sequence entered', () => {
            type("shipped");
            expectLoading("shipped");
            expectNoResults("shipped");
        })

        it('should clear the input and dropdown when clicked off after typing a character sequence that has no results', () => {
            type("shipped");
            clickOff();
            expectNoDropdown("");
        })

        it('should display the results that equal the exact character sequence entered', () => {
            type("Shipping and Receiving");
            expectLoading("Shipping and Receiving");
            expectResults('Shipping and Receiving', ["Shipping and Receiving"]);
        })

        it('should clear the input and dropdown when clicked off after typing a character sequence that is an exact match to a result', () => {
            type("Shipping and Receiving");
            clickOff();
            expectNoDropdown("");
        })
    })

    describe("Query Failures", () => {

    })

    describe("Hover States", () => {

    })

    describe("Initial Selection States", () => {

    })

    describe("Retyping States", () => {

    })

    describe("Reselection States", () => {

    })

    describe("Cookies", () => {

    })
});
