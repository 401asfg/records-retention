import SearchableDropdown from './SearchableDropdown';
import '@testing-library/jest-dom';
import { render, screen, fireEvent, act, waitFor } from '@testing-library/react';
import React, { useState } from 'react';
import axios from 'axios';

jest.mock('axios');

const mockData = [
    {
        "id": 1,
        "name": "Engineering Labs"
    },
    {
        "id": 2,
        "name": "Medical Testing"
    },
    {
        "id": 3,
        "name": "Admin Offices"
    },
    {
        "id": 4,
        "name": "Shipping and Receiving"
    },
    {
        "id": 5,
        "name": "Shipping or Receiving"
    },
    {
        "id": 6,
        "name": "History"
    }
]

const mockResults = {
    "s": [
        {
            "id": 1,
            "name": "Engineering Labs"
        },
        {
            "id": 2,
            "name": "Medical Testing"
        },
        {
            "id": 4,
            "name": "Admin Offices"
        },
        {
            "id": 5,
            "name": "Shipping and Receiving"
        },
        {
            "id": 6,
            "name": "Shipping or Receiving"
        },
        {
            "id": 8,
            "name": "History"
        }
    ],
    "z": [],
    "ship": [
        {
            "id": 4,
            "name": "Shipping and Receiving"
        },
        {
            "id": 5,
            "name": "Shipping or Receiving"
        }
    ],
    "shipped": [],
    "Shipping and Receiving": [
        {
            "id": 4,
            "name": "Shipping and Receiving"
        }
    ],
    "o": [
        {
            "id": 3,
            "name": "Xylophone Room"
        },
        {
            "id": 4,
            "name": "Admin Offices"
        },
        {
            "id": 6,
            "name": "Shipping or Receiving"
        },
        {
            "id": 7,
            "name": "Yellow Painting Room"
        },
        {
            "id": 8,
            "name": "History"
        },
        {
            "id": 9,
            "name": "Automotive Repair"
        }
    ]
};

describe(SearchableDropdown, () => {
    function SearchableDropdownMock(props) {
        const [departmentId, setDepartmentId] = useState(props.initDepartmentId);
        return <SearchableDropdown
            sourceRoute="api/departments"
            selectedOptionId={departmentId}
            setSelectedOptionId={setDepartmentId}
        />
    }

    async function renderSearchableDropdown(initDepartmentId) {

        await waitFor(() => {
            render(<SearchableDropdownMock initDepartmentId={initDepartmentId} />);
        });
    }

    function expectNoDropdown(input) {
        expect(screen.getByTestId("searchable-dropdown-input")).toHaveValue(input);
        expect(screen.queryByTestId("searchable-dropdown-list")).toBeNull();
    }

    describe("Initialization", () => {
        function mockAxiosResponse(initDepartmentId) {
            if (typeof initDepartmentId !== 'number' || initDepartmentId <= 0 || initDepartmentId > mockData.length) {
                axios.get.mockResolvedValue({ data: { data: { name: "" } } });
                return;
            }

            const result = mockData[initDepartmentId - 1];
            axios.get.mockResolvedValue({ data: { data: result } });
        }

        it("should have an empty input and no dropdown if given a null initial selection option id", async () => {
            mockAxiosResponse(null);
            await renderSearchableDropdown(null);
            await expectNoDropdown("");
        })

        it("should have a query corresponding to the given id and no dropdown if given a valid initial selection option id in the input and no dropdown", async () => {
            mockAxiosResponse(1);
            await renderSearchableDropdown(1);
            await expectNoDropdown("Engineering Labs");
        })

        it("should have an empty input and no dropdown if the given initial selection option id does not correspond to a valid selection option", async () => {
            mockAxiosResponse(9999);
            await renderSearchableDropdown(9999);
            await expectNoDropdown("");
        })

        it("should have an empty input and no dropdown if the given initial selection option id is not an integer", async () => {
            mockAxiosResponse("not an integer");
            await renderSearchableDropdown("not an integer");
            await expectNoDropdown("");
        })

        // TODO: submission testing needs to check what happens if the initial id is invalid or out of range
        // FIXME: find a way to check id
    })

    async function type(input) {
        await waitFor(() => { fireEvent.change(screen.getByTestId("searchable-dropdown-input"), { target: { value: input } }); });
    }

    describe("Initial Typing States", () => {
        function mockAxiosResponse(query) {
            axios.get.mockResolvedValue({ data: { data: mockResults[query] } });
        }

        function clickOn() {
            act(() => { fireEvent.click(screen.getByTestId("searchable-dropdown-input")); });
        }

        function clickOff() {
            act(() => { fireEvent.click(document); });
        }

        function expectInput(input) {
            expect(screen.getByTestId("searchable-dropdown-input")).toHaveValue(input);
        }

        function expectSearchableDropdown(input, dropdownType) {
            expectInput(input);
            expect(screen.queryByTestId("searchable-dropdown-" + dropdownType)).toBeInTheDocument();
        }

        function expectNoResults(input) {
            expectSearchableDropdown(input, "no-results");
        }

        async function expectResults(input) {
            expectInput(input);

            mockResults[input].forEach((result, i) => {
                expect(screen.getByTestId("searchable-dropdown-result-" + i)).toHaveTextContent(result.name);
            })
        }

        // TODO: test when clicking off at different points

        beforeEach(async () => {
            await renderSearchableDropdown(null);
        })

        it("should not display the dropdown when the input is initially clicked", async () => {
            mockAxiosResponse("");
            clickOn();
            expectNoDropdown("");
        })

        it('should clear the input and dropdown when clicked off right after clicking on', async () => {
            mockAxiosResponse("");
            clickOn();
            clickOff();
            expectNoDropdown("");
        })

        it('should display all the results that contain the first character entered', async () => {
            mockAxiosResponse("s");
            await type("s");
            expectResults("s");
        })

        // it('should clear the input and dropdown when clicked off after typing one character', async () => {
        //     mockAxiosResponse("s");
        //     await type("s");
        //     clickOff();
        //     expectNoDropdown("");
        // })

        it('should display a results empty message when no results contain the first character entered', async () => {
            mockAxiosResponse("z");
            await type("z");
            expectNoResults("z");
        })

        it('should display all the results that contain the character sequence entered', async () => {
            mockAxiosResponse("ship");
            await type("ship");
            expectResults("ship", [
                'Shipping and Receiving',
                'Shipping or Receiving'
            ]);
        })

        // it('should clear the input and dropdown when clicked off after typing a valid character sequence', async () => {
        //     mockAxiosResponse("ship");
        //     await type("ship");
        //     clickOff();
        //     expectNoDropdown("");
        // })

        it('should display a results empty message when no results contain the character sequence entered', async () => {
            mockAxiosResponse("shipped");
            await type("shipped");
            expectNoResults("shipped");
        })

        // it('should clear the input and dropdown when clicked off after typing a character sequence that has no results', () => {
        //     type("shipped");
        //     clickOff();
        //     expectNoDropdown("");
        // })

        it('should display the results that equal the exact character sequence entered', async () => {
            mockAxiosResponse("Shipping and Receiving");
            await type("Shipping and Receiving");
            expectResults('Shipping and Receiving', ["Shipping and Receiving"]);
        })

        // it('should clear the input and dropdown when clicked off after typing a character sequence that is an exact match to a result', () => {
        //     type("Shipping and Receiving");
        //     clickOff();
        //     expectNoDropdown("");
        // })

        // // TODO: test that searchable select can send requests to actual network
        // // TODO: test loading
    })

    function mockAxiosResponse(query) {
        axios.get.mockResolvedValue({ data: { data: mockResults[query] } });
    }

    function hoverDropdownItem(index) {
        waitFor(() => {
            act(() => { fireEvent.mouseEnter(screen.getByTestId("searchable-dropdown-result-" + index)); });
        });
    }

    function unhoverDropdown() {
        waitFor(() => {
            act(() => { fireEvent.mouseEnter(screen.getByTestId("searchable-dropdown-input")); });
        });
    }

    function expectDropdownItemNotHighlighted(index) {
        expect(screen.getByTestId("searchable-dropdown-result-" + index)).not.toHaveStyle("background-color: #00BFFF");
    }

    const indexes = [0, 1, 2, 3, 4, 5];

    function expectOnlyDropdownItemHighlighted(index) {
        // FIXME: decouple this from the page styling
        expect(screen.getByTestId("searchable-dropdown-result-" + index)).toHaveStyle("background-color: #00BFFF");

        indexes.forEach((i) => {
            if (i !== index) expectDropdownItemNotHighlighted(i);
        });
    }

    function expectNoDropdownItemsHighlighted() {
        indexes.forEach((i) => {
            expectDropdownItemNotHighlighted(i);
        });
    }

    describe("Hover States", () => {
        beforeEach(() => {
            renderSearchableDropdown(null);
        })

        it("should not have any highlighted items in the dropdown when it is first opened", async () => {
            mockAxiosResponse("o");
            await type("o");
            expectNoDropdownItemsHighlighted();
        })

        it("should highlight the first item in the dropdown when it is hovered over", async () => {
            mockAxiosResponse("o");
            await type("o");
            hoverDropdownItem(0);
            expectOnlyDropdownItemHighlighted(0);
        })

        // it("should highlight the next item and unhighlight the first item in the dropdown when the cursor moves to it from the first item", async () => {
        //     mockAxiosResponse("o");
        //     await type("o");
        //     hoverDropdownItem(0);
        //     hoverDropdownItem(1);
        //     expectOnlyDropdownItemHighlighted(1);
        // })

        // it("should unhighlight all items when the cursor moves off the dropdown", () => {
        //     type("o");
        //     unhoverDropdown();
        //     expectNoDropdownItemsHighlighted();
        // })

        // it("should highlight a middle item in the list when the cursor moves onto it from being off the dropdown", () => {
        //     type("o");
        //     hoverDropdownItem(4);
        //     expectDropdownItemHighlighted(4);
        // })

        // it("should highlight the item in the list that the cursor lands on when the cursor wasn't moved, but the dropdown was scrolled through, while unhighlighting the item that was scrolled from", () => {
        //     type("e");
        //     hoverDropdownItem(0);
        //     expectDropdownItemHighlighted(3);
        // })
    })

    // describe("Initial Selection States", () => {

    // })

    // describe("Retyping States", () => {

    // })

    // describe("Reselection States", () => {

    // })

    // describe("Query Failures", () => {

    // })
});
