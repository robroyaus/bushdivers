import React, { useState } from 'react'
import AirportMap from '../../Shared/Components/Airport/AirportMap'
import { Link } from '@inertiajs/inertia-react'
import AppLayout from '../../Shared/AppLayout'

const AirportDetail = ({ airport, metar, aircraft }) => {
  const [icao, setIcao] = useState()

  const handleAirportChange = (e) => {
    setIcao(e.target.value)
  }

  const renderAircraftStatus = (status) => {
    switch (status) {
      case 1:
        return 'Available'
      case 2:
        return 'Reserved'
      case 3:
        return 'In Use'
    }
  }

  const renderRunwayText = (surface) => {
    switch (surface) {
      case 'A':
        return 'Asphalt'
      case 'B':
        return 'Bituminous'
      case 'C':
        return 'Concrete'
      case 'CE':
        return 'Cement'
      case 'CR':
        return 'Water'
      case 'G':
        return 'Grass'
      case 'GR':
        return 'Gravel'
      case 'M':
        return 'Macadam'
      case 'S':
        return 'Sand'
      case 'T':
        return 'Tarmac'
      case 'W':
        return 'Water'
      default:
        return 'Unknown'
    }
  }
  return (
    <div className="p-4">
      <div className="w-1/6 mb-2 flex items-center">
        <input id="airport" type="text" placeholder="ICAO" className="form-input form inline-block" value={icao} onChange={handleAirportChange} />
        <Link href={`/airports/${icao}`} className="ml-2 btn btn-secondary">Go</Link>
      </div>
      { !airport
        ? <h1>Airport Search</h1>
        : <h1>{airport.name} - {airport.identifier}</h1>
      }
      { airport && (
      <div className="flex flex-col lg:flex-row justify-between">
        <div className="lg:w-1/2">
          <div className="rounded shadow p-1 lg:p-4 mt-2 bg-white mx-2">
            <div className="flex justify-between overflow-x-auto">
              <div className="flex flex-col items-center my-2 mx-4">
                <div className="text-sm">ICAO</div>
                <div className="text-xl">{airport.identifier} {airport.longest_runway_surface === 'W' && <span className="material-icons md-18">anchor</span>}</div>
              </div>
              <div className="flex flex-col items-center my-2 mx-4">
                <div className="text-sm">Size</div>
                <div className="text-xl">{airport.size}</div>
              </div>
              <div className="flex flex-col items-center my-2 mx-4">
                <div className="text-sm">Country</div>
                <div className="text-xl">{airport.country}</div>
              </div>
              <div className="flex flex-col items-center my-2 mx-4">
                <div className="text-sm">Latitude</div>
                <div className="text-xl">{airport.lat}</div>
              </div>
              <div className="flex flex-col items-center my-2 mx-4">
                <div className="text-sm">Longitude</div>
                <div className="text-xl">{airport.lon}</div>
              </div>
              <div className="flex flex-col items-center my-2 mx-4">
                <div className="text-sm">Altitude</div>
                <div className="text-xl">{airport.altitude}ft</div>
              </div>
            </div>
          </div>
          {airport.longest_runway_length && (
            <div className="rounded shadow p-4 mt-2 bg-white mx-2">
              <div className="flex items-center">
                <i className="material-icons mr-2">add_road</i>
                <span>{renderRunwayText(airport.longest_runway_surface)} {airport.longest_runway_length}ft x {airport.longest_runway_width}ft</span>
              </div>
            </div>
          )}
          <div className="rounded shadow p-4 mt-2 bg-white mx-2">
            <div className="flex items-center">
              {metar
                ? (
                  <>
                    <i className="material-icons mr-2">light_mode</i>
                    <span>{metar}</span>
                  </>
                  )
                : <div>No METAR available</div>
              }

            </div>
          </div>
          <div className="rounded shadow p-4 mt-2 bg-white mx-2 overflow-x-auto">
            <div className="text-lg">Available Aircraft</div>
            <table className="mt-2 table table-auto table-condensed">
              <thead>
              <tr>
                <th>Registration</th>
                <th>Aircraft</th>
                <th>Hub</th>
                <th>Fuel</th>
                <th>Status</th>
              </tr>
              </thead>
              <tbody>
              {aircraft && aircraft.map((ac) => (
                <tr key={ac.id}>
                  <td><Link href={`/aircraft/${ac.id}`}>{ac.registration}</Link></td>
                  <td>{ac.fleet.manufacturer} {ac.fleet.name} ({ac.fleet.type})</td>
                  <td>{ac.hub_id}</td>
                  <td>{ac.fuel_onboard}</td>
                  <td>{renderAircraftStatus(ac.state)}</td>
                </tr>
              ))}
              </tbody>
            </table>
          </div>
        </div>
        <div className="lg:w-1/2 rounded shadow p-4 mt-2 bg-white mx-2">
          <AirportMap airport={airport} size="large" />
        </div>
      </div>
      )}
    </div>
  )
}

AirportDetail.layout = page => <AppLayout children={page} title="Airport Details" heading="Airport Details" />

export default AirportDetail
